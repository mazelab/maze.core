<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_MessageManager
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_MessageManager
{

    /**
     * contains error messages
     * 
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * Registry object provides storage for shared objects.
     * 
     * @var Core_Model_MessageManager
     */
    private static $_manager = null;
    
    /**
     * Class name of the singleton registry object.
     * 
     * @var string
     */
    private static $_managerClassName = 'Core_Model_MessageManager';
    
    /**
     * contains notify messages
     * @var array
     */
    protected $_notifyMessages = array();
    
    /**
     * contains success messages
     * 
     * @var array
     */
    protected $_successMessages = array();

    /**
     * Translate view helper
     * 
     * @var Zend_View_Helper_Translate
     */
    protected $_translator;
    
    /**
     * Return Translate view helper
     *
     * @return Zend_View_Helper_Translate
     */
    protected function _getTranslator()
    {
        if (!$this->_translator) {
            $this->_translator = new Zend_View_Helper_Translate();
        }
        
        return $this->_translator;
    }
    
   /**
     * adds error message as simple string
     * 
     * @param string $message
     * @return void
     */
    public function addError($message)
    {
        $errorMessages = $this->getErrors();
        $messageVars = array();
        
        if(func_num_args() > 1) {
            $messageVars = func_get_args();
            unset($messageVars[0]);
        }
        
        if (!($message = $this->translate($message, $messageVars)) ||
                in_array($message, $errorMessages)){
            return;
        }

        $errorMessages[] = $message;
        $this->setErrors($errorMessages);
    }
    
    /**
     * adds notification as simple string
     * 
     * @param string $message
     * @return void
     */
    public function addNotification($message)
    {
        $notifyMessages = $this->getNotifications();
        $messageVars = array();
        
        if(func_num_args() > 1) {
            $messageVars = func_get_args();
            unset($messageVars[0]);
        }
        
        if (!($message = $this->translate($message, $messageVars)) ||
                in_array($message, $notifyMessages)){
            return;
        }

        $notifyMessages[] = $message;
        $this->setNotifications($notifyMessages);
    }
    
    /**
     * adds success message as simple string
     * 
     * @param string $message
     * @return void
     */
    public function addSuccess($message)
    {
        $successMessages = $this->getSuccess();
        $messageVars = array();
        
        if(func_num_args() > 1) {
            $messageVars = func_get_args();
            unset($messageVars[0]);
        }
        
        if (!($message = $this->translate($message, $messageVars)) ||
                in_array($message, $successMessages)){
            return;
        }

        $successMessages[] = $message;
        $this->setSuccess($successMessages);
    }
    
    /**
     * adds error messages from the given Zend_Form
     * 
     * @param Zend_Form $form
     * @return Core_Model_MessageManager
     */
    public function addZendFormErrorMessages(Zend_Form $form)
    {
        foreach ($form->getElementsAndSubFormsOrdered() as $element) {
            if ($element instanceof Zend_Form_SubForm) {
                $this->addZendFormErrorMessages($element);
            } elseif ($element instanceof Zend_Form_Element) {
                foreach ($element->getMessages(null, true) as $message) {
                    $this->addError($message);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * returns all error messages
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->_errorMessages;
    }
    
    /**
     * Retrieves the default registry instance.
     *
     * @return Core_Model_MessageManager
     */
    public static function getInstance()
    {
        if (self::$_manager === null) {
            self::init();
        }

        return self::$_manager;
    }
    
    /**
     * returns messages of all types diveded by kind of message
     * 
     * available keys:
     *  errors
     *  notifications
     *  successes
     * 
     * @return array
     */
    public function getMessages()
    {
        $messages = array();

        $messages['errors'] = $this->getErrors();
        $messages['notifications'] = $this->getNotifications();
        $messages['successes'] = $this->getSuccess();
        
        return $messages;
    }
    
    /**
     * returns all notifications
     * 
     * @return array
     */
    public function getNotifications()
    {
        return $this->_notifyMessages;
    }
    
    /**
     * returns all success messages
     * 
     * @return array
     */
    public function getSuccess()
    {
        return $this->_successMessages;
    }
    
    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init()
    {
        self::setInstance(new self::$_managerClassName());
    }

    /**
     * resets seted messages
     * 
     * @return void
     */
    public function reset()
    {
        $this->setErrors(array());
        $this->setNotifications(array());
        $this->setSuccess(array());
    }
    
    /**
     * overwrittes existing error messages with the given array
     * 
     * @param array $errorMessages
     * @reurn void
     */
    public function setErrors($errorMessages)
    {
        $this->_errorMessages = $errorMessages;
    }

    /**
     * Sets own instanziation
     *
     * @param Core_Model_MessageManager $instance
     * @return void
     */
    public static function setInstance(Core_Model_MessageManager $instance)
    {
        self::$_manager = $instance;
    }
    
    /**
     * overwrittes existing notifications with the given array
     * 
     * @param array $notifyMessages
     * @reurn void
     */
    public function setNotifications($notifyMessages)
    {
        $this->_notifyMessages = $notifyMessages;
    }
    
    /**
     * overwrittes existing success messages with the given array
     * 
     * @param array $successMessages
     * @reurn void
     */
    public function setSuccess($successMessages)
    {
        $this->_successMessages = $successMessages;
    }
    
    /**
     * translate a given message
     * 
     * @param string $message
     * @param array $messageVars
     * @return string translated message
     */
    public function translate($message, array $messageVars)
    {
        if(!is_string($message)) {
            return null;
        }
        
        return $this->_getTranslator()->translate($message, $messageVars);
    }
    
}
