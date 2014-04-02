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
        $this->addElement("text", "host", array(
            "label" => "Server",
            "value" => MongoDb_Mongo::DEFAULT_HOST,
            "style" => "width:15%;",
            "validators" => array(
                new Zend_Validate_Hostname(
                    array(
                        "allow" => Zend_Validate_Hostname::ALLOW_ALL
                    )
                )
            )
        ));
        $this->addElement("text", "port", array(
            "style" => "width:8.88%;",
            "validators" => array(
                array("Digits")
            ),
            "value" => MongoDb_Mongo::DEFAULT_PORT
        ));
    }
}

