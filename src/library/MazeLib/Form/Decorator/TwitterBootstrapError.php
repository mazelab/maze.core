<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Form_Decorator_TwitterBootstrapError
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Form_Decorator_TwitterBootstrapError extends Zend_Form_Decorator_Abstract
{
    
    /**
     * Render errors
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $errors = $element->getMessages();
        if (empty($errors)) {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $errors    = $view->twitterBootstrapErrors($errors, $this->getOptions());

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $errors;
            case self::PREPEND:
                return $errors . $separator . $content;
        }
    }
    
}