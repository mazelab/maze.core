<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_AddNews
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_AddNews extends Zend_Form
{
    protected $_elementDecorators = array(
        "ViewHelper",
        "TwitterBootstrapError"
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
                if (empty($tag)){
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
            "class"    => "span12",
            "rows"     => "9",
            "label"    => "Content *"
        ));

        $this->addElement("text", "title", array(
            "require" => true,
            "label"   => "Headline *",
            "class"   => "span12"
        ));

        $this->addElement("checkbox", "sticky", array(
            "label" => "Sticky"
        ));

        $this->addElement("text", "teaser", array(
            "label"       => "Teaser",
            "class"       => "span12"
        ));

        $this->addElement("select", "status", array(
            "require"      => true,
            "multiOptions" => Core_Form_News::$status,
            "value"        => array(Core_Model_NewsManager::STATUS_CLOSED),
            "style"        => "display:none;"
        ));

        $this->setElementDecorators($this->_elementDecorators);
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