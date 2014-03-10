<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_News
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_News extends Zend_Form
{
    /**
     * available status messages
     *
     * @var array
     */
    static public $status = array(
        Core_Model_NewsManager::STATUS_PUBLIC => "Public",
        Core_Model_NewsManager::STATUS_DRAFT  => "Draft",
        Core_Model_NewsManager::STATUS_CLOSED => "Closed"
    );

    protected $_elementDecorators = array(
        "TwitterBootstrapError",
        "ViewHelper"
    );

    protected $_tagSubForm = "tags";

    /**
     * adds tag fields to the form
     * 
     * @param  string $data
     * @return Core_Form_News
     */
    public function addTagsFields($data)
    {
        if (!is_null($data)) {
            $this->getSubForm($this->_tagSubForm)->setOptions(array("isArray" => true));

            foreach ($data as $key => $tag){
                if (empty($key)){
                    continue;
                }

                $this->getSubForm($this->_tagSubForm)->addElement("text", (string) $key, array(
                    "class"  => "jsEditableAdditionalFields",
                    "helper" => "formTextAsSpan",
                    "value"  => $tag
                ))->setElementDecorators(array(
                    "ViewHelper"
                ));
            }
        }

        return $this;
    }

    public function init()
    {
        $this->addPrefixPath("MazeLib_Form_Decorator_", "MazeLib/Form/Decorator/", "decorator");

        $this->addSubForm(new Zend_Form, $this->_tagSubForm);

        $this->addElement("textarea", "content", array(
            "required" => true,
            "helper"   => "formTextAsSpan",
            "class"    => "span12 jsEditableTextareas",
            "label"    => "Content"
        ));

        $this->addElement("text", "title", array(
            "require" => true,
            "helper"  => "formTextAsSpan",
            "label"   => "Headline",
            "class"   => "span12 jsEditable"
        ));

        $this->addElement("text", "teaser", array(
            "label"   => "Teaser",
            "jsLabel" => "Teaser",
            "class"   => "jsEditable",
            "helper"  => "formTextAsSpan"
        ));

        $this->addElement("checkbox", "sticky", array(
            "label" => "Sticky"
        ));

        $this->addElement("select", "status", array(
            "require"      => true,
            "multiOptions" => self::$status,
            "value"        => array(Core_Model_NewsManager::STATUS_CLOSED)
        ));
    }

    /**
     * adds tags field elements for the message
     * 
     * @param  array $data
     * @return Core_Form_News
     */
    public function setTagFields($data)
    {
        if (is_array($data) && key_exists("tags", $data)){
            $this->addTagsFields($data["tags"]);
        }

        return $this;
    }
}