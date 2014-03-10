<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Facade_MazeBean
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Facade_MazeBean 
    extends MazeLib_View_Facade_Bean
{
    
    /**
     * returns all or conflicts of a certain status of this structure
     * 
     * @param int $status lookup for a certain status
     * @return array
     */
    public function getConflicts($status = null)
    {
        if(!$this->_rawStructure instanceof MazeLib_Bean) {
            return array();
        }
        
        return $this->_rawStructure->getConflicts($status);
    }
    
    /**
     * checks if the given property has a (certain) conflicted status
     * 
     * @param string $propertyPath
     * @param int $status check for certain status
     * @return boolean 
     */
    public function hasConflict($propertyPath, $status = null)
    {
        if(!$this->_rawStructure instanceof MazeLib_Bean) {
            return false;
        }
        
        return $this->_rawStructure->hasConflict($propertyPath, $status);
    }
    
    /**
     * checks if conflicts/certain conflict exists in this structure
     * 
     * @param int $status
     * @return boolean
     */
    public function isConflicted($status = null)
    {
        if(!$this->_rawStructure instanceof MazeLib_Bean) {
            return false;
        }
        
        return $this->_rawStructure->isConflicted($status);
    }
    
}
