<?php
namespace app\modules\editor\models;

use app\db\CategoryType;
use yii\db\ActiveRecord;

class TreeNode extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'href',
                    'keywords',
                    'text',
                    'title',
                    'description',
                    'name',
                    'alt',
                    'image'
                ],
                'string'
            ],
            [['date_created', 'last_updated'], 'safe'],
            [['parent_id', 'position'], 'integer'],
            [['hidden'], 'boolean'],
            [['type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'href' => 'Href',
            'keywords' => 'Keywords',
            'text' => 'Text',
            'title' => 'Title',
            'date_created' => 'Date Created',
            'last_updated' => 'Last Updated',
            'description' => 'Description',
            'parent_id' => 'Parent ID',
            'name' => 'Name',
            'hidden' => 'Hidden',
            'alt' => 'Alt',
            'image' => 'Image',
            'type' => 'Type',
            'position' => 'Position',
        ];
    }

    public function group($array)
    {
        $resultArray = [];
        foreach ($array as $item) {
            if (!in_array($item, $resultArray)) {
                $resultArray[] = $item;
            }
        }

        return $resultArray;
    }

    public function getParent()
    {
        return self::findOne(['id' => $this->parent_id]);
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return self::find()->where(['parent_id' => $this->id])->all() != [];
    }

    /**
     * @return array|TreeNode[]
     */
    public function getChildren()
    {
        return self::find()->where(['parent_id' => $this->id])->all();
    }

    /**
     * @param TreeNode[] $parents
     * @return array
     */
    private function buildTree($parents)
    {

        //region sort folders on top
        /** @var TreeNode[] $newParents */
        $newParents = [];
        foreach ($parents as $pa) {
            if ($pa->hasChildren()) {
                $newParents[] = $pa;
            }
        }
        foreach ($parents as $pa) {
            if (!$pa->hasChildren()) {
                $newParents[] = $pa;
            }
        }
        $parents = $newParents;
        //endregion

        $toReturn = [];
        //$toReturn['debug'] = $this->debug($parents);
        foreach ($parents as $parent) {
            $title = $parent->hidden ? $parent->name . "(h)" : $parent->name;
            if ($parent->hasChildren()) {
                $children = $this->buildTree($parent->getChildren());
                $toReturn[] = [
                    "title" => $title,
                    "db_id" => $parent->id,
                    "db_parent_id" => $parent->parent_id,
                    "children" => $children
                ];
            } else {
                $toReturn[] = [
                    "title" => $title,
                    "db_id" => $parent->id,
                    "db_parent_id" => $parent->parent_id
                ];
            }
        }
        return $toReturn;
    }


    public function setHiddenRecursive()
    {
        $this->hidden = true;
        if ($this->hasChildren()) {
            $children = $this->getChildren();
            foreach ($children as $child) {
                $child->setHiddenRecursive();
            }
        }
        if ($this->save()) {
            return 'Success';
        } else {
            return 'Error';
        }
    }

    public function setVisibleRecursive()
    {
        $this->hidden = false;
        if ($this->hasChildren()) {
            $children = $this->getChildren();
            foreach ($children as $child) {
                $child->setVisibleRecursive();
            }
        }
        if ($this->save()) {
            return 'Success';
        } else {
            return 'Error';
        }
    }

    /**
     * @return array|\app\db\CategoryType[]
     */
    public function getPriceCategories()
    {
        return $this->hasMany(CategoryType::className(), ['id' => 'category_type_id'])
            ->viaTable('article_category_type', ['article_id' => 'id']);
    }

    public function debug($var)
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }

    public function getTree($debug = false)
    {

        if ($debug) {
            $parents = $this->find()->where(['parent_id' => null])->all();
            $pArray = [];
            foreach ($parents as $par) {
                $pArray[] = $par;
            }

            return $this->buildTree($pArray);
        }

        //$parent = self::findOne('376');

        $parents = $this->find()->where(['parent_id' => null])->all();
        $pArray = [];
        foreach ($parents as $par) {
            $pArray[] = $par;
        }

        return $this->buildTree($pArray);
    }
}