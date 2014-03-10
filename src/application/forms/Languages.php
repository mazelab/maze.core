<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Languages
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Languages extends Zend_Form
{

    public function init()
    {
        //  @todo dynamic geter
        $languages = array(
            'de_DE' => 'Deutsch',
            'en_US' => 'English'
        );
        
        $this->addElement("select", "language", array(
            "required" => "true",
            "Multioptions" => $languages
        ));
    }


}

