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
        $this->addElement("text", "dbUsername", array(
            "label" => "username",
        ));
        $this->addElement("password", "dbPassword", array(
            "label" => "password",
        ));
    }

    /**
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (array_key_exists("dbUsername", $value) && !empty($value["dbUsername"])) {
            $this->dbPassword->setRequired(true);
        }

        return parent::isValid($value);
    }
}

