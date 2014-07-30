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
        $database = new Zend_Form_SubForm();
        $database->addElement("text", "name", array(
            "required" => "true",
            "label" => "Database Name"
        ));
        $database->addElement("text", "prefix", array(
            "label" => "Database Prefix",
            "validators" => array(
                array("Alnum")
            )
        ));
        $database->addElement("text", "host", array(
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
        $database->addElement("text", "port", array(
            "class" => "cssInstallDatabasePort",
            "validators" => array(
                array("Digits")
            ),
            "value" => MongoDb_Mongo::DEFAULT_PORT
        ));
        $database->addElement("text", "username", array(
            "label" => "username",
            "required" => "true"
        ));
        $database->addElement("password", "password", array(
            "label" => "password",
            "required" => "true"
        ));

        $this->addSubForm($database, "database");
    }

    /**
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (empty($value["password"]) && empty($value["username"])) {
            $this->getSubForm("database")->password->setRequired(false);
            $this->getSubForm("database")->username->setRequired(false);
        }

        return parent::isValid($value);
    }
}

