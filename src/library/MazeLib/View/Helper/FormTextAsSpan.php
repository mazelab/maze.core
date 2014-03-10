<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Helper_FormTextAsSpan
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Helper_FormTextAsSpan extends Zend_View_Helper_FormElement
{
    /**
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @return string The element XHTML.
     */
    public function formTextAsSpan($name, $value = null, $attribs = null)
    {
        $id = null;
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        $translator = $this->getTranslator();
        if (isset($attribs['jsLabel']) && $translator !== null){
            $attribs['jsLabel'] = $translator->translate($attribs['jsLabel']);
        }

        $xhtml = '<span' . $this->_htmlAttribs($attribs)
               . ' name="' . $this->view->escape($name) . '"'
               . ' id="' . $this->view->escape($id) . '">'
               .  $this->view->escape($value)
               . '</span>';

        return $xhtml;
    }
}
