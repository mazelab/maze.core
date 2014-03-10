<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Helper_Navigation_MenuAsSelect
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Helper_Navigation_MenuAsSelect extends Zend_View_Helper_Navigation_Menu
{

    /**
     * @param Zend_Navigation_Container $container
     * @return Core_View_Helper_MenuAsSelect 
     */
    public function menuAsSelect(Zend_Navigation_Container $container = null)
    {
        if ($container !== null) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container   container to render
     * @param  string                    $selectClass     CSS class for first UL
     * @param  string                    $indent      initial indentation
     * @param  int|null                  $minDepth    minimum depth
     * @param  int|null                  $maxDepth    maximum depth
     * @param  bool                      $onlyActive  render only active branch?
     * @return string
     */
    protected function _renderMenu(Zend_Navigation_Container $container, $selectClass, $indent, $minDepth, $maxDepth, $onlyActive)
    {
        $html = '';

        // find deepest active
        if ($found = $this->findActive($container, $minDepth, $maxDepth)) {
            $foundPage = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
                        RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            } else if ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages() ||
                                is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);

            if ($depth > $prevDepth) {
                // start new select tag
                if ($selectClass && $depth == 0) {
                    $selectClass = ' class="' . $selectClass . '"';
                } else {
                    $selectClass = '';
                }
                $html .= $myIndent . '<select' . $selectClass . '>' . self::EOL;
            } else if ($prevDepth > $depth) {
                // close option/select tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </option>' . self::EOL;
                    $html .= $ind . '</select>' . self::EOL;
                }
                // close previous option tag
                $html .= $myIndent . '    </option>' . self::EOL;
            } else {
                // close previous option tag
                $html .= $myIndent . '    </option>' . self::EOL;
            }

            // render option tag and page
            $selected = $isActive ? ' selected' : '';
            $value = $page->getHref() ? ' value="' . $page->getHref() . '"' : '';

            $html .= $myIndent . '    <option' . $selected . $value . '>' . self::EOL
                    . $myIndent . '        ' . $this->htmlify($page) . self::EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open select/option tags
            for ($i = $prevDepth + 1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat('        ', $i - 1);
                $html .= $myIndent . '    </option>' . self::EOL
                        . $myIndent . '</select>' . self::EOL;
            }
            $html = rtrim($html, self::EOL);
        }

        return $html;
    }

}

