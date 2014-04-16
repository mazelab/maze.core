<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_System
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_System extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );

    public function init()
    {
        $this->addPrefixPath("MazeLib_Form_Decorator_", "MazeLib/Form/Decorator/", "decorator")
             ->setElementDecorators($this->_elementDecorators)
             ->initOperatordata()
             ->initCompany()
             ->initDatabase();
    }

    /**
     * initialize the company field
     * 
     * @param  array $defaults
     * @return Core_Form_System
     */
    public function initCompany()
    {  
        $this->addElement("text", "company", array(
            "label"   => "company",
            "jsLabel" => 'company',
            "class"   => "jsEditable",
            "helper"  => "formTextAsSpan"
        ));

        return $this;
    }

    /**
     * initialize the database fields
     * 
     * @param array $defaults
     * @return Core_Form_System
     */
    public function initDatabase()
    {
        $database = new Zend_Form;
        $database->addPrefixPath("MazeLib_Form_Decorator_", "MazeLib/Form/Decorator/", "decorator")
                 ->setElementDecorators($this->_elementDecorators)
                 ->setElementsBelongTo("database");

        $database->addElement("text", "database", array(
            "label" => "Database Name"
        ));

        $database->addElement("text", "collectionPrefix", array(
            "label" => "Database Prefix",
            "validators" => array(
                array("Alnum")
            )
        ));

        $database->addElement("text", "host", array(
            "label" => "Database Server",
            "class" => "cssSystemDatabaseHost",
            "validators" => array(
                new Zend_Validate_Hostname(
                    array(
                        "allow" => Zend_Validate_Hostname::ALLOW_ALL
                    )
                )
            )
        ));

        $database->addElement("text", "port", array(
            "class" => "cssSystemDatabasePort",
            "validators" => array(
                array("Digits")
            )
        ));

        $database->addElement("text", "username", array(
            "label" => "username",
        ));

        $database->addElement("password", "password", array(
            "label" => "password",
        ));

        $this->addSubForm($database, "database");

        return $this;
    }

    /**
     * initialize the system operatordata
     * 
     * @return Core_Form_System
     */
    public function initOperatordata()
    {
        $mail = new Zend_Form;
        $mail->setOptions(array("elementsBelongTo" => "mail"))
             ->addPrefixPath("MazeLib_Form_Decorator_", "MazeLib/Form/Decorator/", "decorator")
             ->setElementDecorators($this->_elementDecorators);

        $mail->addElement("radio", "smtpEnabled", array(
            "label"   => "SMTP",
            "multiOptions" => array(
                "0" => "deactivate",
                "1" => "activate"
            ),
            "value" => array("")
        ));

        $from = new Zend_Form;
        $from->setOptions(array("elementsBelongTo" => "from"))
             ->addPrefixPath("MazeLib_Form_Decorator_", "MazeLib/Form/Decorator/", "decorator")
             ->setElementDecorators($this->_elementDecorators);

        $from->addElement("text", "name", array(
            "jsLabel" => "Name",
            "label"   => "Sender",
            "helper"  => "formTextAsSpan",
            "class"   => "jsEditable"
            
        ));

        $from->addElement("text", "email", array(
            "jsLabel" => "E-mail address *",
            "validators" => array(
                array("EmailAddress")
            ),
            "helper"  => "formTextAsSpan",
            "class"   => "jsEditable"
            
        ));

        $smtp = new Zend_Form;
        $smtp->addPrefixPath("MazeLib_Form_Decorator_", "MazeLib/Form/Decorator/", "decorator")
             ->setOptions(array("elementsBelongTo" => "smtp"))
             ->setElementDecorators($this->_elementDecorators);

        $smtp->addElement("text", "host", array(
            "label"   => "Hostname",
            "jsLabel" => "Hostname",
            "class"   => "jsEditable",
            "helper"  => "formTextAsSpan"
        ));

        $smtp->addElement("text", "port", array(
            "label"   => "Port",
            "jsLabel" => "Port",
            "validators" => array(
                array("Int")
            ),
            "class"   => "jsEditable",
            "helper"  => "formTextAsSpan"
        ));

        $smtp->addElement("text", "username", array(
            "label"   => "username",
            "jsLabel" => "username",
            "class"   => "jsEditable",
            "helper"  => "formTextAsSpan"
        ));

        $smtp->addElement("password", "password", array(
            "label"   => "password"
        ));

        $smtp->addElement("radio", "auth", array(
            "label"   => "Authentication",
            "multiOptions" => array(
                "plain"   => "PLAIN",
                "login"   => "LOGIN",
                "crammd5" => "CRAM-MD5"
            ),
            "value" => array("")
        ));

        $smtp->addElement("radio", "ssl", array(
            "label"   => "Security",
            "multiOptions" => array(
                ""    => "None",
                "tls" => "TLS",
                "ssl" => "SSL"
            ),
            "value" => array("")
        ));

        $mail->addSubForm($smtp, "smtp");
        $mail->addSubForm($from, "from");
        $this->addSubForm($mail, "mail");

        return $this;
    }

    /**
     * Set default values for elements
     *
     * Sets values for all elements specified in the array of $defaults.
     *
     * @param  array $defaults
     * @return Core_Form_System
     */
    public function setDefaults(array $defaults)
    {
        if (array_key_exists("mongodb", $defaults)){
            $defaults["database"] = $defaults["mongodb"];
        }

        parent::setDefaults($defaults);

        return $this;
    }

}
