<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_EmailManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_EmailManager
{
    /**
     * mail message body
     *
     * @var string
     */
    protected $_body;

    /**
     * mail subject
     *
     * @var string 
     */
    protected $_subject;

    /**
     * mail character set
     *
     * @var string 
     */
    protected $_charset = "utf-8";

    /**
     * recipient addresses
     *
     * @var array 
     */
    protected $_addresses = array();

    /**
     * sender of this email
     *
     * @var array
     */
    protected $_sender = array();

    /**
     * mail adapter
     *
     * @var Zend_Mail|mixed
     */
    protected $_adapter;

    /**
     * email connection object
     *
     * @var Zend_Mail_Transport_Abstract|null 
     */
    protected $_transport;

    /**
     * exception stack
     *
     * @var Exception
     */
    protected $_exception;

    /**
     * zend translation object
     *
     * @var Zend_Translate
     */
    protected $_translate;

    /**
     * smtp transport configuration
     *
     * @var array
     */
    protected $_smtpOption = array();

    /**
     * body content type of mail 
     *
     * @var string
     */
    protected $_contentType = "text";

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (!empty($options)){
            $this->setOptions($options);
        }

        if (Zend_Registry::isRegistered("Zend_Translate")){
            $this->_setTranslate(Zend_Registry::get("Zend_Translate"));
        }

        $this->_fetchMazeConfig();
    }

    /**
     * fetch the maze config for mail options
     * 
     * @return Core_Model_EmailManager
     */
    protected function _fetchMazeConfig()
    {
        if (($mazeConfig = Core_Model_DiFactory::getConfig())){

            // if smtp config available and activated, sets the smtp configuration
            if ($mazeConfig->getData("mail/smtpEnabled") && $mazeConfig->getData("mail/smtp")){
                $this->setSmtpOptions($mazeConfig->getData("mail/smtp"));
            }

            if ($mazeConfig->getData("mail/from/email")){
                $this->setFrom($mazeConfig->getData("mail/from/email"), $mazeConfig->getData("mail/from/name"));
            }
        }

        return $this;
    }

    /**
     * get the translation object
     * 
     * @return null|Zend_Translate_Adapter
     */
    protected function _getTranslate()
    {
        if ($this->_translate == null){
            $this->_setTranslate(new Zend_Translate);
        }

        return $this->_translate->getAdapter();
    }

    /**
     * set the mail manager dapter
     * 
     * @param  Zend_Mail $adapter
     * @return Core_Model_EmailManager
     */
    protected function _setAdapter(Zend_Mail $adapter = null)
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     * sets the zend translation object
     * 
     * @param  Zend_Translate $translation
     * @return Core_Model_EmailManager
     */
    protected function _setTranslate(Zend_Translate $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     * adds an email
     * 
     * @param  string $email
     * @param  string $name name for recipient
     * @return Core_Model_EmailManager
     */
    public function addTo($email, $name = null)
    {
        $addresses = $this->getTo();
        $addresses[] = array((string) $name => $email);

        $this->setTo($addresses);

        return $this;
    }

    /**
     * returns the email adapter
     * 
     * @return Zend_Mail
     */
    public function getAdapter()
    {
        if (!$this->_adapter){
            $this->_setAdapter(new Zend_Mail($this->_charset));
        }

        return $this->_adapter;
    }

    /**
     * get the email body message
     * 
     * @return null|string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * get character set of email
     * 
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * return the body content type
     * 
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     * retrieve the exception stack
     *
     * @return null|Exception
     */
    public function getException()
    {
        return $this->_exception;
    }

    /**
     * returns the sender of email
     * 
     * @param  null|string $property [email|name]
     * @return null|string
     */
    public function getFrom($property = null)
    {
        if (is_null($property)){
            return $this->_sender;
        }

        if (array_key_exists($property, $this->_sender)){
            return $this->_sender[$property];
        }

        return null;
    }

    /**
     * returns the smtp options
     * 
     * @param  null|string $property
     * @return mixed
     */
    public function getSmtpOptions($property = null)
    {
        if (is_null($property)){
            return $this->_smtpOption;
        }

        if (array_key_exists($property, $this->_smtpOption)){
            return $this->_smtpOption[$property];
        }

        return null;
    }

    /**
     * get the subject of the email
     * 
     * @return null|string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * returns all to-header and recipient
     * 
     * @return array
     */
    public function getTo()
    {
        return $this->_addresses;
    }

    /**
     * returns email transport object
     * 
     * @return Zend_Mail_Transport_Abstract 
     */
    public function getTransport()
    {
        if ($this->_transport == null){

            if (!$this->getSmtpOptions()){
                $transport = new Zend_Mail_Transport_Sendmail();
            }else {
                $smtp = $this->getSmtpOptions("config");
                if (array_key_exists("password", $smtp)){
                    $cryptManager = Core_Model_DiFactory::getCryptManager();
                    $smtp["password"] = $cryptManager->decrypt($smtp["password"]);
                }
                $transport = new Zend_Mail_Transport_Smtp($this->getSmtpOptions("host"), $smtp);
            }

            $this->setTransport($transport);
        }

        return $this->_transport;
    }

    /**
     * has an exception been registered
     *
     * @return boolean
     */
    public function hasException()
    {
        return !is_null($this->_exception);
    }

    /**
     * sends this email
     * 
     * @param  Zend_Mail_Transport_Abstract $transport
     * @return boolean
     */
    public function send($transport = null)
    {
        $this->_exception = null;
        if ($transport == null || !$transport instanceof Zend_Mail_Transport_Abstract){
            $transport = $this->getTransport();
        }

        $charset = $this->getCharset();
        $adapter = $this->getAdapter();
        $adapter->clearSubject()
                ->setSubject($this->getSubject());

        /* already set in {@link _fetchMazeConfig()}; */
        if ($adapter->getFrom() == null && $this->getFrom("email")){
            $adapter->setFrom($this->getFrom("email"), $this->getFrom("name"));
        }

        foreach ($this->getTo() as $recipient){
            $adapter->addTo($recipient);
        }

        if ($this->_contentType == "html"){
            $adapter->setBodyHtml($this->getBody(), $charset);
        }else {
            $adapter->setBodyText($this->getBody(), $charset);
        }

        try {
            $adapter->send($transport);
        } catch (Exception $e) {
            $this->_exception = $e;
            return false;
        }

        return true;
    }

    /**
     * sets the email body message as plain text
     * 
     * @param  string $text
     * @param  boolean $html body message is html ?
     * @return Core_Model_EmailManager
     */
    public function setBody($text, $html = false)
    {
        if ($html){
            $this->setContentType("html");
        }

        $this->_body = $text;

        return $this;
    }

    /**
     * sets email character set
     * 
     * @param  string $charset
     * @return Core_Model_EmailManager
     */
    public function setCharset($charset)
    {
        $this->_setAdapter(null);
        $this->_charset = $charset;

        return $this;
    }

    /**
     * sets the body type of email
     *
     * @param  string $contest
     * @return Core_Model_EmailManager
     */
    public function setContentType($type = "text")
    {
        if (!in_array($type, array("text", "html"))){
            $type = $this->_contentType;
        }

        $this->_contentType = $type;

        return $this;
    }

   /**
     * sets the sender name of email
     * 
     * @param  string|array $email
     * @param  string $name
     * @return Core_Model_EmailManager
     */
    public function setFrom($email, $name = null)
    {
        if (is_array($email)){
            foreach ($email as $name => $address){
                $email = $address;
            }
        }

        if (is_string($email)){
            $this->_sender = array(
                "email" => $email,
                "name"  => $name
            );
        }

        return $this;
    }

    /**
     * set options en masse
     * 
     * @param  array $options
     * @return Core_Model_EmailManager
     */
    public function setOptions(array $options = array())
    {
        foreach ($options as $option => $params){
            $method = "set". ucfirst($option);

            if (method_exists($this, $method)){
                call_user_func(array($this, $method), $params);
            }
        }

        return $this;
    }

    /**
     * use smtp on the email adapter
     * 
     * example:
     * <pre>array(
     *   "host"     => "smtp.localhost",
     *   "auth"     => "plain",
     *   "username" => "username",
     *   "password" => "password",
     *   "ssl"      => "tls",
     *   "port"     => 465
     * );</pre>
     * 
     * @param  string $host
     * @param  null|array $config
     * @return Core_Model_EmailManager
     */
    public function setSmtpOptions(array $config = array())
    {
        if (array_key_exists("host", $config)){
            $host = $config["host"];
            unset($config["host"]);

            $this->setTransport();
            $this->_smtpOption = array(
                "host"   => $host,
                "config" => $config
            );
        }

        return $this;
    }

    /**
     * sets the email subject
     * 
     * @param  string $subject
     * @return Core_Model_EmailManager
     */
    public function setSubject($subject)
    {
        if ($this->_getTranslate()){
            $subject = $this->_getTranslate()->translate($subject);
        }

        $this->_subject = $subject;

        return $this;
    }

    /**
     * sets the email to-header and recipient´s
     * 
     * @param  array $addresses
     * @return Core_Model_EmailManager
     */
    public function setTo(array $addresses = null)
    {
        $this->_addresses = $addresses;

        return $this;
    }

    /**
     * sets all administrators email addresses
     * 
     * @return Core_Model_EmailManager
     */
    public function setToAdmins()
    {
        $adminManager  = Core_Model_DiFactory::getAdminManager();
        $mailAddresses = array();

        foreach($adminManager->getAdmins() as $admin) {
            if ($admin->getEmail()){
                $mailAddresses[] = $admin->getEmail();
            }
        }

        $this->setTo($mailAddresses);

        return $this;
    }

    /**
     * sets all clients email addresses
     * 
     * @return Core_Model_EmailManager
     */
    public function setToClients()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        $mailAddresses = array();

        foreach($clientManager->getClients() as $client) {
            if ($client->getEmail()){
                $mailAddresses[] = $client->getEmail();
            }
        }

        $this->setTo($mailAddresses);

        return $this;
    }

    /**
     * sets transport or the internal mail function
     * 
     * @param  null|Zend_Mail_Transport_Abstract $transport
     * @return Core_Model_EmailManager
     */
    public function setTransport(Zend_Mail_Transport_Abstract $transport = null)
    {
        $this->_transport = $transport;

        return $this;
    }
}
