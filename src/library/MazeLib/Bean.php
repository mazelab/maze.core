<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Bean
 * 
  * this object helps to differentiate different value states from backend and
 * a node/server. Conflicts in a structure can be identified and resolved.
 * 
 * value:
 * last entry in a structure (array/object/...)
 * 
 * values were mapped to:
 * value array(
 *      'value' => $value,
 *      'status' => 2       // important for conflict behavior
 *
 * valid status are:
 * 1 - 1000 -> manuell conflict resolving
 * 2 - 2000 -> maze priorization
 * 3 - 3000 -> remote priorization
 * 
 * @license http://opensource.org/licenses/MIT MIT
 * @see ZendX_AbstractBean
 */
class MazeLib_Bean extends ZendX_AbstractBean
{

    /**
     * status for manual conflict resolving
     */
    CONST STATUS_MANUALLY = 1000;
    
    /**
     * status for remote priorized properties conflict resolving
     */
    CONST STATUS_PRIO_MAZE = 2000;

    /**
     * naming of the local value field in value mapping
     */
    CONST FIELD_LOCAL_VALUE = 'local';

    /**
     * naming of the status field in value mapping
     */
    CONST FIELD_STATUS = 'status';

    /**
     * naming of the remote value  field in value mapping
     */
    CONST FIELD_REMOTE_VALUE = 'remote';

    /**
     * wildcard for every simple (no struct) value in the tier
     */
    CONST PATH_WILDCARD = '*';
    
    /**
     * custom behavior for status
     * 
     * 'propertyPath' => 'default status'
     *
     * @var array
     */
    protected $mapping;

    /**
     * contains found conflicts
     * 
     * @var array
     */
    protected $conflicts = array();
    
    /**
     * flag for allready searched conflicts
     * 
     * @var boolean
     */
    protected $searchedConflicts = false;
    
    /**
     * dissolves given array into property Pathes with its Values
     * 
     * @param array $data
     * @param string|null $parent
     * @return array
     */
    protected function _dissolveArray(array $data, $parent = null, $buildParent = true)
    {
        $dissolvedArray = array();
        
        foreach($data as $key => $value){
            if(!$parent) {
                $path = $key;
            } else {
                $path = $parent . self::PATH_SEPERATOR . $key;
            }
            
            $isMapped = $this->getMapping($path);
            if(!$buildParent) {
                $path = $key;
            }
            
            if ($isMapped) {
                $dissolvedArray[$path] = $value;
            } elseif (is_array($value) ) {
                foreach ($this->_dissolveArray($value, $path) as $path => $value) {
                    $dissolvedArray[$path] = $value;
                }
            } else {
                $dissolvedArray[$path] = $value;
            }
        }
        
        return $dissolvedArray;
    }
    
    /**
     * dissolves the property path into the sub properties.
     * returns an array for foreach usage
     * 
     * @param string $propertyPath
     * @return array
     */
    protected function _dissolvePropertyPath($propertyPath)
    {
        if(mb_strpos($propertyPath, self::PATH_SEPERATOR) === 0) {
            $propertyPath = mb_substr($propertyPath, 1);
        }
        
        return explode(self::PATH_SEPERATOR, $propertyPath);
    }
    
    /**
     * builds wildcard path from the given propertPath
     * 
     * exchanges last tier with wildcard flag
     * 
     * @param string $propertyPath
     * @return string
     */
    protected function _getWildCardPath($propertyPath)
    {
        $dis = $this->_dissolvePropertyPath($propertyPath);
        
        if (count($dis) < 1) {
            return false;
        }

        array_pop($dis);
        $dis[] = self::PATH_WILDCARD;
        
        return implode(self::PATH_SEPERATOR, $dis);
    }

    /**
     * checks that given $property is a value property
     * 
     * @param mixed $property
     * @return boolean
     */
    protected function _isMazeProperty($property)
    {
        if (is_array($property) && (key_exists(self::FIELD_STATUS, $property)
                    && key_exists(self::FIELD_LOCAL_VALUE, $property)
                    && key_exists(self::FIELD_REMOTE_VALUE, $property))) {
            return true;
        }

        return false;
    }
    
    /**
     * merge maze property from local side
     * 
     * @param string $path
     * @param mixed $orig original maze value set
     * @param string $update update value
     * @param mixed $remote is this value from remote
     */
    public function _mergeMazeProperty($path, $orig, $update, $remote = false)
    {
        if(!($status = $this->getMapping($path))) {
            return $update;
        }
        
        if ($this->_isMazeProperty($orig)) {
            $merged = $orig;
            
            if($remote && $merged[self::FIELD_REMOTE_VALUE] === $update) {
                return $merged;
            }
        } else {
            $merged[self::FIELD_REMOTE_VALUE] = null;
            $merged[self::FIELD_LOCAL_VALUE] = null;            
        }
        
        if($remote) {
            $merged[self::FIELD_REMOTE_VALUE] = $update;
        } else {
            $merged[self::FIELD_LOCAL_VALUE] = $update;
        }
        
        if($merged[self::FIELD_LOCAL_VALUE] !== $merged[self::FIELD_REMOTE_VALUE]) {
            if(($status / 1000) >= 1) {
                $status = $status / 1000;
            }
            
            if($remote) {
                $status = abs($status);
            } else {
                $status = - abs($status);
            }
        } else {
            if(($status / 1000) < 1) {
                $status = abs($status * 1000);
            }            
        }
        
        $merged[self::FIELD_STATUS] = $status;
        return $merged;
    }
    
    /**
     * resets temporary bean properties
     */
    protected function _reset()
    {
        $this->searchedConflicts = false;
    }
    
    /**
     * resolves dissolved array
     * 
     * break up maze path and  unmap maze values
     * 
     * @param array $disArray
     * @param string|null $parent
     * @param boolean $buildParent shall the parent be build in the return array
     * @param boolean $remote resolve Maze Values as remote
     * @return type
     */
    protected function _resolveArray(array $disArray, $parent = null, $buildParent = true, $remote = false)
    {
        $resArray = array();
        
        foreach($disArray as $key => $value) {
            if(!$parent) {
                $path = $key;
            } else {
                $path = $parent . self::PATH_SEPERATOR . $key;
            }
            
            $isMapped = $this->getMapping($path);
            if(!$buildParent) {
                $path = $key;
            }
            
            if($isMapped && $this->_isMazeProperty($value)) {
                if($remote) {
                    $value = $value[self::FIELD_REMOTE_VALUE];
                } else {
                    $value = $value[self::FIELD_LOCAL_VALUE];
                }
            }
            
            $subValue = & $resArray;
            $disArray = $this->_dissolvePropertyPath($path);
            while(count($disArray) > 0) {
                $subPath = array_shift($disArray);
                if(!is_array($subValue)) {
                    $subValue = array();
                }
                
                $subValue = & $subValue[$subPath];
            }
            
            $subValue = $value;
        }
        
        return $resArray;
    }
    
    /**
     * searches through the array context and identifies conflicts and returns
     * them
     * 
     * @return array
     */
    public function _searchConflicts(array $context)
    {
        $conflicts = array();

        foreach (array_keys($context) as $arrayKey) {
            $arrayVal = $context[$arrayKey];
            $status = null;

            if ($this->_isMazeProperty($arrayVal)) {
                $status = $arrayVal[self::FIELD_STATUS];

                if (is_numeric($status) && ($status / 1000) < 1) {
                    $conflicts[$arrayKey] = $arrayVal;
                }
            } else if (is_array($arrayVal)) {
                $inDeep = $this->_searchConflicts($arrayVal);
                if (!empty($inDeep)) {
                    foreach ($inDeep as $key => $value) {
                        $propertyPath = $arrayKey . self::PATH_SEPERATOR . $key;

                        $conflicts[$propertyPath] = $value;
                    }
                }
            }
        }

        return $conflicts;
    }
    
    /**
     * sets a certain local maze bean property
     * 
     * @param string $path
     * @param mixed $value
     * @param boolean $remote
     */
    protected function _setMazeProperty($path, $value, $remote = false)
    {
        $property = $this;
        foreach ($this->_dissolvePropertyPath($path) as $subProperty) {
            // processing depending on property type
            if (is_array($property)) {
                $property = & $this->_setPropertyArray($property, $subProperty);
            } elseif ($property instanceof ZendX_AbstractBean) {
                $property = & $property->_setPropertyBean($subProperty);
            } elseif (is_object($property)) {
                $property = & $this->_setPropertyGenericObject($property, $subProperty);
            }
        }
        
        // mapping behavior
        if ($this->getMapping($path)) {
            $value = $this->_mergeMazeProperty($path, $property, $value, $remote);
        }

        $property = $value;
    }

    /**
     * sets a certain bean property
     * 
     * @param string $propertyPath
     * @param mixed $value
     */
    protected function _setProperty($propertyPath, $value)
    {
        $property = $this;
        foreach ($this->_dissolvePropertyPath($propertyPath) as $subProperty) {
            // processing depending on property type
            if (is_array($property)) {
                $property = & $this->_setPropertyArray($property, $subProperty);
            } elseif ($property instanceof ZendX_AbstractBean) {
                $property = & $property->_setPropertyBean($subProperty);
            } elseif (is_object($property)) {
                $property = & $this->_setPropertyGenericObject($property, $subProperty);
            }
        }

        $property = $value;
    }
    
    /**
     * unmaps value properties in the given array
     * 
     * @param type $array
     * @return array
     */
    protected function _unmap(array $array)
    {
        $unmaped = array();

        foreach (array_keys($array) as $arrayKey) {
            $arrayVal = $array[$arrayKey];

            if ($this->_isMazeProperty($arrayVal) && key_exists(self::FIELD_LOCAL_VALUE, $arrayVal)) {
                $arrayVal = $arrayVal[self::FIELD_LOCAL_VALUE];
            } else if ($this->_isMazeProperty($arrayVal) && !key_exists(self::FIELD_LOCAL_VALUE, $arrayVal)) {
                $arrayVal = null;
            } else if (is_array($arrayVal)) {
                $arrayVal = $this->_unmap($arrayVal);
            }

            $unmaped[$arrayKey] = $arrayVal;
        }

        return $unmaped;
    }
    
    /**
     * property unset for arrays
     * 
     * @param array $array
     * @param string $key
     * @return boolean
     */
    protected function _unsetPropertyArray(& $array, $key)
    {
        if(isset($array[$key])) {
            unset($array[$key]);
        }
        
        return true;
    }
    
    /**
     * property unset for beans
     * 
     * @param string $property
     * @return boolean
     */
    protected function _unsetPropertyBean($property)
    {
        $propertyName = self::PREFIX_BEAN_PROPERTIES . $property;
        
        if(property_exists($this, $propertyName)) {
            unset($this->$propertyName);
        }
        
        return true;
    }
    
    /**
     * property unset for normal objects
     * 
     * @param object $genericObject
     * @param string $key
     * @return boolean
     */
    protected function _unsetPropertyGenericObject(& $genericObject, $key)
    {
        if(property_exists($genericObject, $key)) {
            unset($genericObject->$key);
        }
        
        return true;
    }

    /**
     * returns all bean properties in array form. Bean objects wont be dissolved.
     * 
     * @return array 
     */
    public function asArray($unmapped = false)
    {
        $array = array();
        $vars = get_object_vars($this);

        // run through each property first lvl
        foreach ($vars as $property => $propertyValue) {
            
            // match property names
            if (mb_strpos($property, self::PREFIX_BEAN_PROPERTIES) === 0) {
                $array[mb_substr($property, 1)] = $propertyValue;
            }
        }
        
        if ($unmapped) {
            return $array;
        }

        return $this->_unmap($array);
    }

    /**
     * returns all bean properties in array form and dissolves each bean object
     * in it.
     * 
     * @param boolean $unmapped
     * @return array
     */
    public function asDeepArray($unmapped = false)
    {
        $array = array();

        foreach (get_object_vars($this) as $name => $value) {
            if (mb_strpos($name, self::PREFIX_BEAN_PROPERTIES) === 0) {
                $result = $this->$name;

                if (!$unmapped && $this->_isMazeProperty($result)
                        && key_exists(self::FIELD_LOCAL_VALUE, $result)) {
                    $result = $result[self::FIELD_LOCAL_VALUE];
                }

                if (isset($result)) {
                    if ($result instanceof ZendX_AbstractBean) {
                        $result = $result->asDeepArray($unmapped);
                    } elseif (is_array($result)) {
                        foreach ($result as $key => $value) {
                            if ($value instanceof ZendX_AbstractBean) {
                                $value = $value->asDeepArray($unmapped);
                            }
                            $result[$key] = $value;
                        }
                    }
                }
                $array[mb_substr($name, 1)] = $result;
            }
        }

        if ($unmapped) {
            return $array;
        }

        return $this->_unmap($array);
    }

    /**
     * returns all or conflicts of a certain status of this structure
     * 
     * contains:
     * array(
     *      'propertyPath' => array(
     *          'local' => '',
     *          'status' => '',
     *          'remote' => ''
     *      ),
     *      ...
     * )
     * 
     * @param int $status lookup for a certain status
     * @return array
     */
    public function getConflicts($status = null)
    {
        $conflicts = array();
        
        if(abs($status) / 1000 >= 1) {
            $status = $status / 1000;
        }
        
        foreach (array_keys($this->getMappings(true)) as $path) {
            if(($status && $status != $this->getProperty($path . self::PATH_SEPERATOR . self::FIELD_STATUS)) ||
                    !$this->hasConflict($path)) {
                continue;
            } 
            
            $conflicts[$path] = $this->getProperty($path);
        }
        
        return $conflicts;
    }
    
    /**
     * gets local values from mapping and returns them
     * 
     * @return array
     */
    public function getLocalData()
    {
        $remoteData = array();

        foreach(array_keys($this->getMappings(true)) as $path) {
            if(($property = $this->getProperty($path)) && $this->_isMazeProperty($property)) {
                $parent = & $remoteData;
                foreach($this->_dissolvePropertyPath($path) as $subPath) {
                    if(!key_exists($subPath, $parent)) {
                        $parent[$subPath] = array();
                    }
                    $parent = & $parent[$subPath];
                }
                
                $parent = $property[self::FIELD_LOCAL_VALUE];
            }
        }
        
        return $remoteData;
    }
    
    /**
     * gets a certain property.
     * 
     * @param string $path
     * @return mixed
     */
    public function getLocalProperty($path)
    {
        $value = $this->getProperty($path);
        
        if($this->getMapping($path) && $this->_isMazeProperty($value)) {
            return $value[self::FIELD_LOCAL_VALUE];
        } elseif (is_array($value)) {
            return $this->_resolveArray($this->_dissolveArray($value, $path, false), $path, false);
        }
        
        return $value;
    }
    
    /**
     * get status code for a certain maze value by path
     * 
     * @param string $propertyPath
     * @return int|null
     */
    public function getMapping($propertyPath)
    {
        if(!($mapping = $this->getMappings())) {
            return array();
        }
        
        if(!key_exists($propertyPath, $mapping) && (!($propertyPath = $this->_getWildCardPath($propertyPath)) ||
                !key_exists($propertyPath, $mapping))) {
            return null;
        }
        
        return $mapping[$propertyPath];
    }
    
    /**
     * get path of all maze values with status code
     * 
     * @param boolean $dissolveWildcards dissolves all existing wildcard properties
     * @return array
     */
    public function getMappings($dissolveWildcards = false)
    {
        if(!$this->mapping || !is_array($this->mapping)) {
            return array();
        }
        
        if(!$dissolveWildcards) {
            return $this->mapping;
        }

        $mapping = array();
        foreach($this->mapping as $path => $status) {
            if(!($pos = strrpos($path, '/*')) || !($parent = substr($path, 0, $pos))) {
                $mapping[$path] = $status;
                continue;
            }
            
            $property = $this->getProperty($parent);
            if (is_array($property)) {
                foreach(array_keys($property) as $key) {
                    $mapping[$parent . self::PATH_SEPERATOR . $key] = $status;
                }
            }
        }
        
        return $mapping;
    }

    /**
     * gets a certain property.
     * 
     * @param string $propertyPath
     * @param boolean $throwExceptions throws exception if path elements do not exist
     * @return mixed
     */
    public function getProperty($propertyPath, $throwExceptions = false)
    {
        $disProperty = $this->_dissolvePropertyPath($propertyPath);
        
        $property = $this;
        foreach ($disProperty as $subProperty) {
            // processing depending on property type
            try {
                if (is_array($property)) {
                    $property = $this->_getPropertyArray($property, $subProperty);
                } elseif ($property instanceof ZendX_AbstractBean) {
                    $property = $property->_getPropertyBean($subProperty);
                } elseif (is_object($property)) {
                    $property = $this->_getPropertyGenericObject($property, $subProperty);
                } else {
                    $property = NULL;
                }
            } catch (Exception $e) {
                if ($throwExceptions) {
                    throw $e;
                }

                return NULL;
            }
        }

        return $property;
    }
    
    /**
     * gets remote values from mapping and returns them
     * 
     * @return array
     */
    public function getRemoteData()
    {
        $remoteData = array();

        foreach(array_keys($this->getMappings(true)) as $path) {
            if(($property = $this->getProperty($path))) {
                
                $parent = & $remoteData;
                foreach($this->_dissolvePropertyPath($path) as $subPath) {
                    if(!key_exists($subPath, $parent)) {
                        $parent[$subPath] = array();
                    }
                    
                    $parent = & $parent[$subPath];
                }
                
                $parent = $property[self::FIELD_REMOTE_VALUE];
            }
        }
        
        return $remoteData;
    }
    
    /**
     * gets a certain property.
     * 
     * @param string $path
     * @return mixed
     */
    public function getRemoteProperty($path)
    {
        $value = $this->getProperty($path);
        
        if($this->getMapping($path) && $this->_isMazeProperty($value)) {
            return $value[self::FIELD_REMOTE_VALUE];
        } elseif (is_array($value)) {
            return $this->_resolveArray($this->_dissolveArray($value, $path, false), $path, false, true);
        }
        
        return $value;
    }
    
    /**
     * checks if the given property has a positiv conflict
     * 
     * @param string $propertyPath
     * @param boolean $negative check for negative conflict instead
     * @return boolean 
     */
    public function hasConflict($propertyPath, $negative = false)
    {
        if (!$this->getMapping($propertyPath)) {
            return false;
        }
        
        if(!($status = $this->getProperty($propertyPath . self::PATH_SEPERATOR . self::FIELD_STATUS))) {
            return false;
        }

        if($negative && $status >= 0) {
            return false;
        }
        
        if((abs($status) / 1000) >= 1) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks that property exists. The Property also exists if the value is Null.
     * It will only return false if the property don't exists
     * 
     * @param string $propertyPath
     * @return boolean 
     */
    public function hasProperty($propertyPath)
    {
        try{
            $this->getProperty($propertyPath, true);
        } catch (Exception $e) {
            return FALSE;
        }
        
        return TRUE;
    }
    
    /**
     * checks if conflicts/certain conflict exists in this structure
     * 
     * @param int $status
     * @return boolean
     */
    public function isConflicted($status = null)
    {
        if ($this->getConflicts($status)) {
            return true;
        }

        return false;
    }
    
    /**
     * Deploys given $data array into bean properties.
     * Uses the key and value pairs.
     * 
     * @param array $data
     * @throws MazeLib_View_Bean_Exception
     */
    public function setBean($data)
    {
        if(!is_array($data)) {
            return false;
        }

        $this->_reset();
        
        foreach ($this->_dissolveArray($data) as $path => $value) {
            if($this->getMapping($path) && !$this->_isMazeProperty($value)) {
                throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be maze value', array($path)));
            }
            
            $this->_setProperty($path, $value);
        }
    }
    
    /**
     * Deploys given $data array into bean properties.
     * Uses the key and value pairs.
     * 
     * If properties contain a path of a maze value it will be set as an local value
     * 
     * @param array $data
     * @throws MazeLib_View_Bean_Exception
     */
    public function setLocalData(array $data)
    {
        $this->_reset();

        foreach ($this->_dissolveArray($data) as $path => $value) {
            if($this->getMapping($path) && (is_array($value) || is_object($value))) {
                throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be either string, numeric or boolean', array($path)));
            }
            
            $this->_setMazeProperty($path, $value);
        }
    }
    
    /**
     * sets property from local side
     * 
     * @param string $propertyPath
     * @param mixed $value
     * @return void
     * @throws MazeLib_View_Bean_Exception
     */
    public function setLocalProperty($propertyPath, $value)
    {
        $this->_reset();
        
        if(is_array($value) && !$this->_isMazeProperty($value)) {
            return $this->setLocalData($this->_dissolveArray($value, $propertyPath));
        }
        
        if($this->getMapping($propertyPath) && $this->_isMazeProperty($value)) {
            throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be maze value', array($propertyPath)));
        }
        
        $this->_setMazeProperty($propertyPath, $value);
    }

    /**
     * sets a certain value bean property.
     * 
     * dissolves multiple tiers
     * 
     * @param string $propertyPath
     * @param mixed $value
     */
    public function setProperty($propertyPath, $value)
    {
        $this->_reset();

        if($this->getMapping($propertyPath) && !$this->_isMazeProperty($value)) {
            throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be maze value', array($propertyPath)));
        }
        
        if(is_array($value) && !$this->_isMazeProperty($value)) {
            return $this->setBean($this->_dissolveArray($value, $propertyPath));
        }
        
        $this->_setProperty($propertyPath, $value);
    }
    
    /**
     * Deploys given $data array into bean properties.
     * Uses the key and value pairs.
     * 
     * If properties contain a path of a maze value it will be set as an remote value
     * 
     * @param array $data
     * @throws MazeLib_View_Bean_Exception
     */
    public function setRemoteData(array $data)
    {
        $this->_reset();

        foreach ($this->_dissolveArray($data) as $path => $value) {
            if($this->getMapping($path) && (is_array($value) || is_object($value))) {
                throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be either string, numeric or boolean', array($path)));
            }
            
            $this->_setMazeProperty($path, $value, true);
        }

        return TRUE;
    }
    
    /**
     * sets property from remote side
     * 
     * @param string $propertyPath
     * @param mixed $value
     * @return void
     * @throws MazeLib_View_Bean_Exception
     */
    public function setRemoteProperty($propertyPath, $value)
    {
        $this->_reset();
        
        if(is_array($value) && !$this->_isMazeProperty($value)) {
            return $this->setRemoteData($this->_dissolveArray($value, $propertyPath));
        }
        
        if($this->getMapping($propertyPath) && $this->_isMazeProperty($value)) {
            throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be maze value', array($propertyPath)));
        }
        
        $this->_setMazeProperty($propertyPath, $value, true);
    }
    
    /**
     * unset a certain value bean property.
     * 
     * @param string $propertyPath
     * @reutnr boolean
     */
    public function unsetProperty($propertyPath)
    {
        $disProperty = $this->_dissolvePropertyPath($propertyPath);
        $this->_reset();
        
        // follow property path
        $parentProperty = $this;
        foreach ($disProperty as $count => $subProperty) {
            if($count == (count($disProperty) - 1)) {
                // processing depending on property type
                if (is_array($parentProperty)) {
                    return $this->_unsetPropertyArray($parentProperty, $subProperty);
                } elseif ($parentProperty instanceof ZendX_AbstractBean) {
                    return $parentProperty->_unsetPropertyBean($subProperty);
                } elseif (is_object($parentProperty)) {
                    return $this->_unsetPropertyGenericObject($parentProperty, $subProperty);
                }
                
                break;
            }
            
            // processing depending on property type
            if (is_array($parentProperty)) {
                $parentProperty = & $this->_setPropertyArray($parentProperty, $subProperty);
            } elseif ($parentProperty instanceof ZendX_AbstractBean) {
                $parentProperty = & $parentProperty->_setPropertyBean($subProperty);
            } elseif (is_object($parentProperty)) {
                $parentProperty = & $this->_setPropertyGenericObject($parentProperty, $subProperty);
            }
        }        
        
        return false;
    }
    
}
