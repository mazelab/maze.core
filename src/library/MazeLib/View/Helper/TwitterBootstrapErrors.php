<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Helper_TwitterBootstrapErrors
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Helper_TwitterBootstrapErrors extends Zend_View_Helper_FormErrors
{

    /**#@+
     * @var string Element block start/end tags and separator
     */
    protected $_htmlElementStart     = '<div class="alert alert-error"><ul><li>';
    protected $_htmlElementEnd       = '</li></ul></div>';
    
    public function twitterBootstrapErrors($errors)
    {
        return $this->formErrors($errors);
    }
    
}