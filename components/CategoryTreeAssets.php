<?php
namespace app\modules\editor\components;

use yii\web\AssetBundle;

class CategoryTreeAssets extends AssetBundle
{

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset'
    ];

    public $css = [
        'css/nestedList.css'
    ];

    public $js = [
        'js/categoryTypeMain.js'
    ];

    public $publishOptions = [
        'forceCopy' => true,
    ];

    public function init()
    {
        $this->sourcePath = '@app/modules/editor/components/assets';
        parent::init();
    }

}