<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Helper_UrlAvatar
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Helper_UrlAvatar extends Zend_View_Helper_Url
{
    /**
     * returns the url of avatar by the given user id
     * 
     * @param  string $userId user hash
     * @param  integer $size
     * @param  boolean $noCache
     * @return string
     */
    public function urlAvatar($userId, $size = 200, $noCache = true)
    {
        if (!is_integer($size)){
            $size = (integer) $size;
        }

        $cacheHack = $noCache ? "&=". time() : null;
        $urlAvatar = $this->url(array($userId), "avatar")
                   . "?s=$size"
                   . "$cacheHack";

        return $urlAvatar;
    }
}