<?php
use app\components\FancytreeWidget;
use app\modules\editor\components\CategoryTreeWidget;
use mihaildev\ckeditor\CKEditor;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

rmrevin\yii\fontawesome\AssetBundle::register($this);
$model = isset($model) ? $model : null;

?>

<div class="container" id="mySuperUniqueAdminPanelContentId">
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <b>Статьи</b>
                </div>
                <div id="tree" class="panel-body fancytree-colorize-hover fancytree-fade-expander">
                    <style scoped>
                        ul.fancytree-container {
                            border: none;
                        }
                    </style>
                    <?= FancytreeWidget::widget([
                                'options' => [
                                    'activate' => new JsExpression('function(node,data) {
                    				console.log("Activate",data,node);
                    				var id = data.node.data["db_id"];
                    				$.ajax({
                    					type: "POST",
                    					url: "' . Url::to(['node/select']) . '",
                    					beforeSend: function() {
                    						$("#element-form").hide();
                    						$("#loader").show();
                    					},
                    					complete: function() {
                    						$("#element-form").show();
                    						$("#loader").hide();
                    						$("#kaUpdateButton").text("Update this")
                    					},
                    					data: {
                    						id: id
                    					},
                    					success: function(data) {
                    						console.log(data);
                    						window.checkIds(data.categoryIds);
                    						$("#treenode-id").val(data.id);
                    						$("#treenode-title").val(data.title);
                    						$("#treenode-name").val(data.name);
                    						CKEDITOR.instances["treenode-text"].
                    							setData(data.text);
                    						$("#treenode-description").val(data.description);
                    						$("#treenode-href").val(data.href);
                    						$("#treenode-parent_id").
                    							val(data.parent_id);
                    						$("#treenode-keywords").val(data.keywords);
                    						$("#kaDeleteButton").prop("disabled",false);
                    					}
                    				});
                    			}'),
                                    'minExpandLevel' => 2,
                                    'source' => $treeNodes,
                                    'extensions' => ['dnd', 'glyph'],
                                    'dnd' => [
                                        'preventVoidMoves' => true,
                                        'preventRecursiveMoves' => true,
                                        'autoExpandMS' => 400,
                                                'dragStart' => new JsExpression('function(node, data) {
                                            return true;
                                        }'),
                                        'dragEnter' => new JsExpression('function(node, data) {
                                            return true;
                                        }'),
                                        'dragDrop' => new JsExpression('function(node, data) {
                                            data.otherNode.moveTo(node, data.hitMode);
                                            var movable = data.otherNode;
                                            var dest = node;
                                            console.log("Id of movable: "+movable.data.db_id,"id of new dest: "+ dest.data.db_id);
                                            myApp.moveNode("' . Url::to(['node/move']) . '",movable.data.db_id,dest.data.db_id);
                                        }'),
                                    ],
                                    'glyph' => [
                                        'map' => [
                                            "doc" => "fa fa-file-o",
                                            "docOpen" => "fa fa-file-o",
                                            "checkbox" => "fa fa-square-o",
                                            "checkboxSelected" => "fa fa-check-square-o",
                                            "checkboxUnknown" => "fa fa-square",
                                            "dragHelper" => "fa arrow-right",
                                            "dropMarker" => "fa long-arrow-right",
                                            "error" => "fa fa-warning",
                                            "expanderClosed" => "fa fa-caret-right",
                                            "expanderLazy" => "fa fa-angle-right",
                                            "expanderOpen" => "fa fa-caret-down",
                                            "folder" => "fa fa-folder-o",
                                            "folderOpen" => "fa fa-folder-open-o",
                                            "loading" => "fa fa-spinner fa-pulse"
                                        ]
                                    ],
                                ]
                            ]);
                    ?>

                </div>
            </div>

        </div>
        <div class="col-md-8" style="display: none;" id="loader">
            <div class="col-md-12 alert alert-info" role="alert">
                <?= FA::icon('spinner')
                    ->spin()
                    ->pullLeft()
                    ->size(FA::SIZE_4X); ?>
                <h2 style="text-align: center">Loading</h2>
            </div>
        </div>
        <div class="col-md-8" id="element-form">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#home" aria-controls="home" role="tab" data-toggle="tab">
                        Редактирование записи
                    </a>
                </li>
                <li role="presentation">
                    <a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">
                        Связь с категориями цен
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" style="padding-top: 20px"
                     class="tab-pane active" id="home">
                    <?php $form = ActiveForm::begin(['id' => 'nodeForm']); ?>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'id')
                                ->textInput(['disabled' => true]); ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'parent_id'); ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'href'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'title'); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'name'); ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'description'); ?>
                    <?= $form->field($model, 'keywords'); ?>
                    <?= $form->field($model, 'text')
                        ->widget(CKEditor::className(), [
                            'editorOptions' => [
                                'preset' => 'full',
                                'inline' => false,
                            ],
                        ]); ?>
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-3"></div>
                        <div class="col-md-3">
                            <button class="btn btn-default col-md-12 col-sm-12 col-xs-12"
                                    id="kaSetHiddenButton">
                                Set hidden
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-default col-md-12 col-sm-12 col-xs-12"
                                    id="kaSetVisibleButton">
                                Set visible
                            </button>
                        </div>
                    </div>
                    <?php $form->end(); ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="profile"
                     style="padding-top: 20px">
                    <h1>Доступные категории:</h1>

                    <div>
                        <div class="list-group list-group-root well col-md-9"
                             id="categoryTypesWidget">
                            <?= CategoryTreeWidget::widget([
                                "categories" => $categories
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <p>
                                Категории, у которых имеются отмеченные
                                подкатегории выделенны светло синим цветом
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    window.onload = function () {
        window.myApp = {};

        myApp.showAlert = function (status, text) {
            var $container = $('#kaAlertContainer');
            $('#kaAlertText').html(text);
            $container.
            removeClass('alert-success').
            removeClass('alert-danger');
            if (status == 'Success') {
                $container.addClass('alert-success');
                $container.show();
                $('#mySuperUniqueAdminPanelContentId').css('margin-top', '60px');
                setTimeout(function () {
                    $('#mySuperUniqueAdminPanelContentId').css('margin-top', '');
                    $container.hide();
                }, 2000);
            } else {
                $container.addClass('alert-danger');
                $container.show();
                $('#mySuperUniqueAdminPanelContentId').css('margin-top', '60px');
                setTimeout(function () {
                    $('#mySuperUniqueAdminPanelContentId').css('margin-top', '');
                    $container.hide();
                }, 5000);
            }
        };

        myApp.getDataFromForm = function () {
            return {
                id: $("#treenode-id").val(),
                title: $("#treenode-title").val(),
                name: $("#treenode-name").val(),
                nodeClass: $("#treenode-class").val(),
                hidden: $("#treenode-hidden").val(),
                text: CKEDITOR.instances['treenode-text'].getData(),
                description: $("#treenode-description").val(),
                href: $("#treenode-href").val(),
                parent_id: $("#treenode-parent_id").val(),
                keywords: $("#treenode-keywords").val(),
                categoryIds: window.getCheckedCategoryIds()
            }
        };

        myApp.updateNode = function (actionUrl, data) {
            console.log(data);
            $.ajax({
                type: "post",
                url: actionUrl,
                data: data,
                success: function (response) {
                    console.log(response);
                    var text;

                    window.myApp.submitInProcess = false;
                    if (response.isNew) {
                        text = '<b>Node creation:</b> ' + response.status;
                        myApp.showAlert(response.status, text);
                        window.location.reload();
                    } else {
                        text = '<b>Node update:</b> ' + response.status;
                        myApp.showAlert(response.status, text);
                    }
                },
                error: function (qXHR, status, response) {
                    myApp.showAlert(status, response);
                }
            })
        };

        myApp.moveNode = function (actionUrl, nodeId, newParentId) {
            $.ajax({
                type: "post",
                url: actionUrl,
                data: {
                    movable: nodeId,
                    newParent: newParentId
                },
                success: function (responce) {
                    if (responce.status == "Error") {
                        myApp.showAlert(responce.status, '<b>Move status: </b>' + responce.status +
                            " " + responce.message[0]);
                        console.log(responce);
                    } else {
                        myApp.showAlert(responce.status, 'Node ' + nodeId +
                            ' successfully moved to ' + newParentId +
                            ' <b> Reload the page if you lost it </b>')
                    }
                },
                error: function (qXHR, status, response) {
                    myApp.showAlert(status, response);
                }
            })
        };

        $("#kaUpdateButton").click(function (e) {
            e.preventDefault();
            var $form = $('#nodeForm');

            //prevent page reload on validation
            $form.on('submit', function (event) {
                event.preventDefault();
                return false;
            });

            $form.data('yiiActiveForm').submitting = true;
            $form.yiiActiveForm('validate');
            $form.data('yiiActiveForm').submitting = false;
            $form.on('afterValidate', function () {
                console.log('VALIDATION ERRORS COUNT: ', $form.find('.has-error').length);
                if (!$form.find('.has-error').length) {
                    if (!window.myApp.submitInProcess) {
                        window.myApp.submitInProcess = true;
                        myApp.updateNode('<?=Url::to(['node/update']);?>', myApp.getDataFromForm());
                    }
                }
            });
            return false;
        });

        $('#kaNewItemButton').click(function () {
            $('form').find('input[type=text], textarea').val("");
            $('#kaUpdateButton').text('Save this');
            $('#kaDeleteButton').prop('disabled', true);
        });

        $('#kaSetHiddenButton').click(function (e) {
            e.preventDefault();
            $.ajax({
                url: '<?=Url::to(['node/visibility']);?>',
                data: {
                    id: $("#treenode-id").val(),
                    hidden: true
                },
                beforeSend: function () {
                    $("#element-form").hide();
                    $("#loader").show();
                },
                complete: function () {
                    $("#element-form").show();
                    $("#loader").hide();
                },
                type: "POST",
                success: function (data) {
                    $('#kaActionType').text('Set hidden status:');
                    $('#kaActionStatusText').text(data.status);
                    $('#kaInfoModal').modal('show');
                    console.log(data);
                },
                error: function (qXHR, status, response) {
                    myApp.showAlert(status, response);
                }
            });
        });

        $('#kaSetVisibleButton').click(function (e) {
            e.preventDefault();
            $.ajax({
                url: '<?=Url::to(['node/visibility']);?>',
                data: {
                    id: $("#treenode-id").val(),
                    hidden: false
                },
                beforeSend: function () {
                    $("#element-form").hide();
                    $("#loader").show();
                },
                complete: function () {
                    $("#element-form").show();
                    $("#loader").hide();
                },
                type: "POST",
                success: function (data) {
                    $('#kaActionType').text('Set visible status:');
                    $('#kaActionStatusText').text(data.status);
                    $('#kaInfoModal').modal('show');
                    console.log(data);
                },
                error: function (qXHR, status, response) {
                    myApp.showAlert(status, response);
                }
            });
        });

        $('#kaDeleteButton').click(function () {
            if (confirm('Are you sure? This action can not be undone')) {
                console.log('deleting');
                $.ajax({
                    type: "post",
                    url: '<?=Url::to(['node/delete']);?>',
                    data: {
                        id: $("#treenode-id").val()
                    },
                    success: function (response) {
                        console.log(response);
                        var text = '<b>Node delete:</b> ' + response.status;

                        window.myApp.submitInProcess = false;
                        if (response.status == "Success") {
                            window.location.reload();
                        }
                    },
                    error: function (qXHR, status, response) {
                        myApp.showAlert(status, response);
                    }

                })
            }
        });
    }
</script>