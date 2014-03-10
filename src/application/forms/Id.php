<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Id
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Id extends Zend_Form
{

    public function init()
    {
        $this->addElement('text', '_id', array(
            'required' => 'true',
        ));
    }

}

