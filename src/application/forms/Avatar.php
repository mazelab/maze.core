<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Avatar
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Avatar extends Zend_Form
{
    public function init()
    {
        $this->addElement("file", "avatar", array(
            "class" => "jsUserAvatar",
            "decorators" => array("file"),
            "style" => "display:none;",
            "validators" => array(
                array("Size", false, "500kb"),
                array("Count", false, 1),
                array('Extension', false, "jpg,jpeg,gif,png")
            )
        ));
    }

}
