<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_AbstractFacade
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
abstract class MazeLib_View_AbstractFacade extends ZendX_View_AbstractFacade
{
    
    /**
     * default _ignoredDataTypes
     * 
     * @var array
     */
    protected $_ignoredDataTypes = array(
        'MazeLib_View_AbstractFacade',
        'ZendX_View_AbstractFacade',
        'Zend_Form_Element',
        'Zend_Form',
        'Zend_Navigation',
        'Zend_Paginator'
    );
    
    /**
     * universal interface method with context switch or standard html escaping
     *
     * @param string $key
     * @param string $context   the escaping context like
     * - html
     * - nofilter
     * - json
     */
    public function getProperty($key, $context = null)
    {
        $raw = $this->_fetchRawValue(ltrim($key, '/'));
        if ($this->_isIgnoredDataType($raw)) {
            return $raw;
        }
        if (is_null($context)) {
            $context = $this->_escapingContext;
        }
        switch (gettype($raw)) {
            case 'string':
                return new ZendX_View_Facade_String($raw, $this->_view, $context);//$this->_escapeForContext($raw, $context);
                break;
            case 'object':
                if ($raw instanceof MazeLib_Bean) {
                    $property = new MazeLib_View_Facade_MazeBean($raw, $this->_view, $context);
                } else if ($raw instanceof ZendX_AbstractBean) {
                    $property = new MazeLib_View_Facade_Bean($raw, $this->_view, $context);
                } else {
                    $property = new MazeLib_View_Facade_Iterator((array) $raw, $this->_view, $context);
                }
                
                // deploy ignoredDataTypes
                $property->setIgnoredDataTypes($this->getIgnoredDataTypes());
                return $property;
                break;
            case 'array':
                $property = new MazeLib_View_Facade_Iterator($raw, $this->_view, $context);
                
                // deploy ignoredDataTypes
                $property->setIgnoredDataTypes($this->getIgnoredDataTypes());
                return $property;
                break;
            case 'boolean':
            case 'integer':
            case 'double':
                return $raw;
                break;
            case 'NULL':
            case 'unknown type':
            case 'resource':
            default :
                return new ZendX_View_Facade_Null('', $this->_view, $context);
                break;
                
        }
        return new ZendX_View_Facade_String('', $this->_view, $context);
    }

}
