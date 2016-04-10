<?php
namespace app\modules\editor\components;

use app\db\CategoryType;
use yii;

class CategoryTreeWidget extends yii\base\Widget
{

    public $categories = [];

    private static $maxPossibleDepth = 99;

    public $level;

    /**
     * Renders the widget.
     */
    public function run()
    {
        //kind a better option for using hardcoded values
        $this->level = isset($this->level) ? $this->level : self::$maxPossibleDepth;
        $parents = $this->sortParents($this->categories);

        $toReturn = '';
        foreach ($parents as $parent) {
            $title = $parent->name;
            $htmlId = "categoryElementWithId_" . $parent->id;
            if ($parent->hasChildren() && $this->level >= 1) {
                $children = CategoryTreeWidget::widget([
                    "categories" => $parent->getChildren(),
                    "level" => $this->level - 1]);
                $toReturn .= $this->getFolder($htmlId, $title, $children);
            } else {
                $toReturn .= $this->getItem($title, $htmlId);
            }
        }
        CategoryTreeAssets::register($this->view);
        return $toReturn;
    }

    /**
     * @param CategoryType[] $parents
     * @return CategoryType[]
     */
    private function sortParents($parents)
    {
        /** @var CategoryType[] $newParents */
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
        return $newParents;
    }


    private function getFolder($id, $title, $children)
    {
        return "<span class=\"list-group-item\">
						<a href=\"#$id\" data-toggle=\"collapse\" class='myLink'>
						    <i class=\"glyphicon glyphicon-chevron-right\"></i>
						</a>
						<a href=\"#\" 
						    data-toggle=\"collapse\" 
						    class='myLink myCheckbox'
						    categoryElementId='$id'>
                            <i class=\"glyphicon glyphicon-unchecked\" ></i >
				        </a >
						<a class='myLink'>$title</a>
					</span>
					<div class=\"list-group collapse\" id=\"$id\">
						$children
					</div>";
    }

    private function getItem($title, $id)
    {
        return
            "<span class=\"list-group-item\">
                <a href=\"#\" class='myLink myCheckbox' categoryElementId='$id'>
                    <i class=\"glyphicon glyphicon-unchecked\" ></i >
				</a >
                <a class='myLink'>$title</a>
             </span>";
    }
}
