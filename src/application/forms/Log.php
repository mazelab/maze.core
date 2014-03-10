<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Log
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Log extends Zend_Form
{
    
    public function setContextRequirements()
    {
        $this->getElement('contextId')->setRequired(true);
        $this->getElement('action')->setRequired(true);
    }
    
    public function init()
    {
        
        $this->addElement('text', 'type', array(
            'required' => true
        ));
        $this->addElement('text', 'datetime', array(
            'required' => true
        ));
        $this->addElement('text', 'message', array(
            'required' => true
        ));
        $this->addElement('text', 'user', array(
            'required' => true
        ));
        $this->addElement('text', 'data', array(
            'isArray' => true
        ));
        
        $this->addElement('text', 'url');
        $this->addElement('text', 'contextId');
        $this->addElement('text', 'action');
        
        $module = new Zend_Form();
        $module->addElement('text', 'name');
        $module->addElement('text', 'label');
        
        $this->addSubForm($module, 'module');

        $node = new Zend_Form();
        $node->addElement('text', 'id');
        $node->addElement('text', 'label');
        
        $this->addSubForm($node, 'node');

        $client = new Zend_Form();
        $client->addElement('text', 'id');
        $client->addElement('text', 'label');
        
        $this->addSubForm($client, 'client');
        
        $domain = new Zend_Form();
        $domain->addElement('text', 'id');
        $domain->addElement('text', 'label');
        
        $this->addSubForm($domain, 'domain');
    }
    
}

