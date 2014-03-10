<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Form_Element_SelectAttribs
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Form_Element_SelectAttribs extends Zend_Form_Element_Select
{
    public $options = array();
    public $helper = 'selectAttribs';

    /**
     * Adds a new <option>
     * @param string $value value (key) used internally
     * @param string $label label that is shown to the user
     * @param array $attribs additional attributes
     */
    public function setOption($value, $label = '', $attribs = array())
    {
        $value = (string) $value;
        if (!empty($label))
            $label = (string) $label;
        else
            $label = $value;
        $this->options[$value] = array(
            'value' => $value,
            'label' => $label,
            'attribs' => $attribs
        );

        return $this;
    }

}