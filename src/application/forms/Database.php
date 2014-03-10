<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Database
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Database extends Zend_Form
{

    public function init()
    {
        $this->addElement("text", "dbName", array(
            "required" => "true",
            "label" => "database name"
        ));
        $this->addElement("text", "dbCollectionPrefix", array(
            "label" => "db collection prefix",
            "validators" => array(
                array("Alnum")
            )
        ));
    }
}

