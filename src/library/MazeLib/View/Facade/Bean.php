<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Facade_Bean
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Facade_Bean 
    extends MazeLib_View_AbstractFacade
{
    /**
     * @var ZendX_AbstractBean 
     */
    protected $_rawStructure = null;
    
    /**
     * deploys given data into the inner structure
     * 
     * @param mixed $data 
     */
    protected function _initData($data)
    {
        switch (gettype($data)) {
            case 'object':
                if ($data instanceof ZendX_AbstractBean) {
                    $this->_rawStructure = $data;
                }
                break;
            case 'array':
                $this->_rawStructure = new MazeLib_View_Facade_Iterator($data, $this->_view);
                break;
        }
        
        if (null === $this->_rawStructure) {
            $this->_rawStructure = new MazeLib_View_Facade_Iterator(null,  $this->_view);
        }
    }
    
    /**
     * Covers using in string context
     * 
     * @return string of classname 
     */
    public function __toString()
    {
        return 'ViewFacade';
    }
    
    /**
     * gets the unescaped property
     * 
     * @param string $key
     * @return mixed 
     */
    protected function _fetchRawValue($key)
    {
        if($this->_rawStructure instanceof ZendX_AbstractBean) {
            return $this->_rawStructure->getProperty(ltrim($key, '/'));
        }
        
        return $this->_rawStructure->getProperty(ltrim($key, '/'));
    }

}
