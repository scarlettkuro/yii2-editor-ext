<?php

namespace app\modules\editor\services;

/**
 * Description of S3ImageFilter
 *
 * @author kuro
 */
class S3ImageFilter
{

    private $s3;               //s3 client
    private $bucket;           //bucket name
    private $cacheDir;         //temp storage for images
    private $jpegQuality;          //quality of jpeg
    private $uploadAmazon;

    public function __construct($bucket, $options = NULL)
    {
        //initialize s3 client
        $this->s3 = \Yii::$app->awssdk->getAwsSdk()->createS3();
        //create bucket if not exist (temp)
        $this->bucket = $this->createBucket($bucket);

        $this->cacheDir = isset($options['cacheDir']) ?
            __DIR__ . $options['cacheDir'] :
            __DIR__ . "/cache";
        $this->uploadAmazon = isset($options['cacheDir']) ?
            'uploadAmazonFromFile' :
            'uploadAmazonFromBuffer';
        $this->jpegQuality = isset($options['jpegQuality']) ? $options['jpegQuality'] : 90;
    }


    private function createBucket($bucket)
    {
        if (!$this->s3->doesBucketExist($bucket)) {
            $this->s3->createBucket(['Bucket' => $bucket]);
            $this->s3->waitUntil('BucketExists', ['Bucket' => $bucket]);
        }

        return $bucket;
    }

    /**
     * Check if file already on Amazon
     * @param string $imageUrl
     * @return boolean
     */
    private function isAmazon($imageUrl)
    {
        return preg_match("/http:\/\/(.*).s3.amazonaws.com\/(.*)/", $imageUrl);
    }

    /**
     * Upload file to Amazon S3 using memory buffer
     * @param string $imageUrl
     * @return string s3 image url
     */
    private function uploadAmazonFromBuffer($imageUrl)
    {
        $imagePath = $this->loadImage($imageUrl);
        $filename = pathinfo($imagePath)['basename'];

        $this->s3->registerStreamWrapper();
        $context = stream_context_create(['s3' => ['ACL' => 'public-read' /*'ContentType'=> 'image/jpeg'*/]]);
        file_put_contents('s3://' . $this->bucket . '/' . $filename, $this->loadImageContents($imageUrl), 0, $context);

        return $this->s3->getObjectUrl($this->bucket, $filename);;
    }

    /**
     * Upload file to Amazon S3 using file cache
     * @param string $imageUrl
     * @return string s3 image url
     */
    private function uploadAmazonFromFile($imageUrl)
    {
        $imagePath = $this->loadImage($imageUrl);
        $filename = pathinfo($imagePath)['basename'];

        // Upload a file
        $result = $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => $filename,
            'SourceFile' => $imagePath,
            'ACL' => 'public-read',
        ]);

        return $result['ObjectURL'];
    }

    /**
     * Convert image to jpeg and save it as file to cacheDir
     * @param string $imageUrl
     * @return string path of cache file
     */
    private function loadImage($imageUrl)
    {
        $filename = pathinfo($imageUrl)['basename'];
        //change extension to jpg
        $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '.jpg', $filename);

        $localPath = $this->cacheDir . "/$filename";

        $image = imagecreatefromstring(file_get_contents($imageUrl));
        imagejpeg($image, $localPath, $this->jpegQuality);
        imagedestroy($image);

        return $localPath;
    }

    /**
     * Convert image to jpeg and save it's contents to buffer
     * @param string $imageUrl
     * @return jpeg data
     */
    private function loadImageContents($imageUrl)
    {
        $filename = pathinfo($imageUrl)['basename'];
        //change extension to jpg
        $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '.jpg', $filename);

        $image = imagecreatefromstring(file_get_contents($imageUrl));

        //catch stream to buffer
        ob_start();
        imagejpeg($image);
        $imageFileContents = ob_get_contents();
        ob_end_clean();

        return $imageFileContents;
    }

    /**
     * Pass text with <img>'s here
     * It loads external images to amazon and replace urls
     * @param string $text original text
     * @return string text with new img urls
     */
    public function filterImages($text)
    {
        return preg_replace_callback("/(<img[^>]*src *= *[\"']?)([^\"']*)/i", function ($matches) {
            $originalImageUrl = $matches[2];
            $imageUrl = $this->isAmazon($originalImageUrl) ?
                $originalImageUrl :
                call_user_func([$this, $this->uploadAmazon], /*params...*/
                    $originalImageUrl);
            return $matches[1] . " $imageUrl";
        }, $text);
    }
}
