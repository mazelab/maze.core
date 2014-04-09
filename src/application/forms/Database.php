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
        $this->addElement("text", "database", array(
            "required" => "true",
            "label" => "Database Name"
        ));
        $this->addElement("text", "collectionPrefix", array(
            "label" => "Database Prefix",
            "validators" => array(
                array("Alnum")
            )
        ));
        $this->addElement("text", "host", array(
            "label" => "Database Server",
            "value" => MongoDb_Mongo::DEFAULT_HOST,
            "class" => "cssInstallDatabaseHost",
            "validators" => array(
                new Zend_Validate_Hostname(
                    array(
                        "allow" => Zend_Validate_Hostname::ALLOW_ALL
                    )
                )
            )
        ));
        $this->addElement("text", "port", array(
            "class" => "cssInstallDatabasePort",
            "validators" => array(
                array("Digits")
            ),
            "value" => MongoDb_Mongo::DEFAULT_PORT
        ));
        $this->addElement("text", "username", array(
            "label" => "username",
            "required" => "true"
        ));
        $this->addElement("password", "password", array(
            "label" => "password",
            "required" => "true"
        ));
    }

    /**
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (empty($value["password"]) && empty($value["username"])) {
            $this->password->setRequired(false);
            $this->username->setRequired(false);
        }

        return parent::isValid($value);
    }
}

