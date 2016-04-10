<?php

namespace app\modules\editor;

use Yii;
use yii\base\Module;

class TreeEditor extends Module
{
    public $controllerNamespace = 'app\modules\editor\controllers';

    public function init()
    {
        $this->layout = "main";

        Yii::$app->assetManager->bundles['yii\bootstrap\BootstrapAsset'] = [
            'js' => ['js/bootstrap.min.js']
        ];
        parent::init();
        // custom initialization code goes here
    }
}
