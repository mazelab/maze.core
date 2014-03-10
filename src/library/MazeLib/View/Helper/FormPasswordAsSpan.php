<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Helper_FormPasswordAsSpan
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Helper_FormPasswordAsSpan extends Zend_View_Helper_FormElement
{
    /**
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @return string The element XHTML.
     */
    public function formPasswordAsSpan($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
		$endTag= '>';
		$value = ($value == null ? "<i>Click to edit</i>" : $value);

        $xhtml = '<span'.  $this->_htmlAttribs($attribs)
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'. $endTag
                . '</span>';

        return $xhtml;
    }
}
