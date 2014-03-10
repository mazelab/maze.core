<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Bean_TestBean
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Bean_TestBean extends MazeLib_Bean
{
    
    protected $mapping = array(
        'array/key' => self::STATUS_MANUALLY,
        'maze/val' => self::STATUS_PRIO_MAZE,
        'wildcard/*' => self::STATUS_PRIO_MAZE
    );
    
}