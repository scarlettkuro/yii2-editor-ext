<?php

namespace app\modules\editor\controllers;

use app\db\CategoryType;
use app\db\User;
use app\modules\editor\models\TreeNode;
use app\modules\editor\services\S3ImageFilter;
use yii;
use yii\web\Controller;
use yii\web\Response;

class NodeController extends Controller
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        /** @var User $user */
        $user = User::findOne(Yii::$app->user->id);

        if (!$user->hasRole('ROLE_ADMIN') || Yii::$app->user->isGuest) {
            throw new yii\web\ForbiddenHttpException('You don\'t have access to this area');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {

        $tree = new TreeNode();
        $nodes = $tree->getTree();
        $debugInfo = $tree->getTree(true);
        $id = Yii::$app->request->get("id");
        $node = empty($id) ? $tree :
            $tree->find()->where(['id' => $id])->one();
        $topCategories = CategoryType::find()->where(['parent_id' => null])->all();

        return $this->render('index', [
            'treeNodes' => $nodes,
            "categories" => $topCategories,
            'model' => $node,
            'debugInfo' => $debugInfo
        ]);
    }

    public function actionSelect()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            Yii::$app->response->format = Response::FORMAT_JSON;
            /** @var TreeNode $element */
            $element = TreeNode::findOne(['id' => $id]);

            $categoryIds = [];
            foreach ($element->priceCategories as $priceCategory) {
                $categoryIds[] = $priceCategory->id;
            }

            return [
                'id' => $element->id,
                'title' => $element->title,
                'name' => $element->name,
                'description' => $element->description,
                'text' => $element->text,
                'href' => $element->href,
                'parent_id' => $element->parent_id,
                'categoryIds' => $categoryIds,
                'keywords' => $element->keywords
            ];
        }
        return 'error1';
    }

    public function actionMove()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('movable');
            $newParent = Yii::$app->request->post('newParent');
            Yii::$app->response->format = Response::FORMAT_JSON;
            /** @var TreeNode $element */
            $element = TreeNode::find()
                ->where(['id' => $id])->one();
            $element->parent_id = $newParent;
            if ($element->save()) {
                return [
                    'status' => 'Success'
                ];
            } else {
                return [
                    'status' => 'Error',
                    'message' => $element->getErrors()
                ];
            }

        }
        return 'not here';
    }

    public function actionUpdate()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $title = Yii::$app->request->post('title');
            $name = Yii::$app->request->post('name');
            $description = Yii::$app->request->post('description');

            $s3filter = new S3ImageFilter("mrt-test-bucket");
            $text = $s3filter->filterImages(Yii::$app->request->post('text'));

            $href = Yii::$app->request->post('href');
            $parent_id = Yii::$app->request->post('parent_id');
            $keywords = Yii::$app->request->post('keywords');
            $categoryIds = Yii::$app->request->post('categoryIds');

            if (!empty($id)) {
                /** @var TreeNode $node */
                $node = TreeNode::findOne($id);
                $isNew = false;
            } else {
                $node = new TreeNode();
                $isNew = true;
            }

            $node->title = $title;
            $node->name = $name;
            $node->description = $description;
            $node->text = $text;
            $node->href = $href;
            $node->parent_id = $parent_id;
            $node->keywords = $keywords;

            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($node->save() && is_array($categoryIds)) {
                Yii::$app->db->createCommand()->
                delete('article_category_type', ['article_id' => $node->id])->
                execute();
                foreach ($categoryIds as $catId) {
                    Yii::$app->db->createCommand()
                        ->insert('article_category_type', [
                            'article_id' => $node->id,
                            'category_type_id' => $catId
                        ])->execute();
                }
            }
            return [
                'status' => 'Success',
                'isNew' => $isNew
            ];
        } else {
            return false;
        }
    }

    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            /** @var TreeNode $element */
            $element = TreeNode::findOne($id);
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($element->delete() > 0) {
                return [
                    'status' => 'Success'
                ];
            } else {
                return [
                    'status' => 'Error'
                ];
            }
        } else {
            return false;
        }
    }

    public function actionVisibility()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $hidden = Yii::$app->request->post('hidden');
            /** @var TreeNode $element */
            $element = TreeNode::findOne($id);
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($hidden === 'true') {
                if ($element->setHiddenRecursive()) {
                    return [
                        'status' => 'Success',
                        'hiddenGot' => $element->debug($hidden)
                    ];
                } else {
                    return [
                        'status' => 'Error',
                        'hiddenGot' => $element->debug($hidden)
                    ];
                }
            } else {
                if ($element->setVisibleRecursive()) {
                    return [
                        'status' => 'Success',
                        'setVisible' => 'true',
                        'hiddenGot' => $element->debug($hidden)
                    ];
                } else {
                    return [
                        'status' => 'Error',
                        'hiddenGot' => $element->debug($hidden)
                    ];
                }
            }
        } else {
            return false;
        }
    }

}