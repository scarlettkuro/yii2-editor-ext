<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title>Tree editor</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
				<span class="navbar-brand">Tree editor
					<a href="javascript:{history.back()}">go back</a>
				</span>
            </div>
            <div class="nav navbar-nav navbar-right" style="padding-top: 8px">
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <button id="kaNewItemButton" class="btn btn-success col-md-12">
                        New item
                    </button>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4">
                    <button id="kaDeleteButton" disabled="disabled" class="btn btn-danger col-md-12">
                        Delete this
                    </button>
                </div>

                <div class="col-md-4 col-sm-4 col-xs-4">
                    <button id="kaUpdateButton" class="btn btn-info col-md-12">
                        Save this
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div id="kaAlertContainer" class="alert alert-success col-md-12" role="alert"
                 style="display: none;">
                <button type="button" class="close"
                        data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
					<span id="kaAlertText">

					</span>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 50px; margin-bottom: 20px">
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; altRecipe <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
