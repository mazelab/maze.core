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
 * 1 - 1000 -> manual conflict resolving
 * 2 - 2000 -> maze prioritization
 * 3 - 3000 -> remote prioritization
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
     * behavior for maze bean properties
     * 
     * 'propertyPath' => 'default status'
     *
     * @var array
     */
    protected $mapping = array();

    /**
     * contains found conflicts
     * 
     * @var array
     */
    protected $conflicts = array();
    
    /**
     * flag for already searched conflicts
     * 
     * @var boolean
     */
    protected $searchedConflicts = false;

    /**
     * contains current wildcard paths
     *
     * @var array
     */
    protected $wildcardMapping = array();

    /**
     * checks if given property is part of a wildcard
     *
     * @param $propertyPath
     * @return string wildcard property path
     */
    protected function _buildWildcardMapping($propertyPath)
    {
        $disProperty = $this->_dissolvePropertyPath($propertyPath);
        $result = $status = null;

        for($i = 0; $i < count($disProperty); $i++) {
            $disPropertyAll = $disProperty;
            $disPropertyAll[$i] = self::PATH_WILDCARD;
            $wildcardAll = implode(self::PATH_SEPERATOR, $disPropertyAll);

            $disWildcard[$i] = self::PATH_WILDCARD;
            $wildcardPartial = implode(self::PATH_SEPERATOR, $disWildcard);

            $disPropertyPartial[$i] = $disProperty[$i];
            if(array_key_exists($wildcardAll, $this->mapping)) {
                $result = $propertyPath;
                $status = $this->mapping[$wildcardAll];
            } elseif(array_key_exists($wildcardPartial, $this->mapping)) {
                $result = implode(self::PATH_SEPERATOR, $disPropertyPartial);
                $status = $this->mapping[$wildcardPartial];
            } else {
                $disWildcard[$i] = $disProperty[$i];
            }
        }

        if($status && $result) {
            $this->wildcardMapping[$result] = $status;
        }

        return $result;
    }

    /**
     * dissolves given array into property paths with its Values
     * 
     * @param array $data
     * @param string|null $parent
     * @param boolean $buildParent adds parent as array key on top
     * @return array
     */
    protected function _dissolveArray(array $data, $parent = null, $buildParent = true)
    {
        $dissolvedArray = array();
        
        foreach($data as $key => $value){
            if(!$parent || !$buildParent || !$parent) {
                $path = $key;
            } else {
                $path = $parent . self::PATH_SEPERATOR . $key;
            }

            if (is_array($value) && !($this->getMapping($path) || $this->_isMazeProperty($value))) {
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
     * builds wildcard path from the given propertyPath
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
        if (is_array($property) && (array_key_exists(self::FIELD_STATUS, $property)
                    && array_key_exists(self::FIELD_LOCAL_VALUE, $property)
                    && array_key_exists(self::FIELD_REMOTE_VALUE, $property))) {
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
     * @return array
     */
    protected function _mergeMazeProperty($path, $orig, $update, $remote = false)
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
     * breaks up property path and unwraps only registered maze values
     * 
     * @param array $disArray
     * @param string|null $parent
     * @param boolean $remote resolve Maze Values as remote
     * @return mixed
     */
    protected function _resolveArray(array $disArray, $parent = null, $remote = false)
    {
        if($parent && ($mapping = $this->getMapping($parent)) && $this->_isMazeProperty($disArray)) {
            return $this->_unmap($disArray, $remote);
        }

        $resArray = array();
        foreach($disArray as $key => $value) {
            if($parent === null || $parent === false) {
                $path = $key;
            } else {
                $path = $parent . self::PATH_SEPERATOR . $key;
            }

            if($this->_isMazeProperty($value) && $this->getMapping($path)) {
                $value = $this->_unmap($value, $remote);
            } elseif (is_array($value)) {
                $value = $this->_resolveArray($value, $path, $remote);
            }

            $resArray[$key] = $value;
        }

        return $resArray;
    }

    /**
     * get all existing conflicting maze values
     *
     * @return array
     */
    public function _getConflicts()
    {
        $conflicts = array();

        foreach (array_keys(array_merge($this->mapping, $this->wildcardMapping)) as $path) {
            if(!$property = $this->_getProperty($path)) {
                continue;
            }

            $propertyStatus = $property[self::FIELD_STATUS];
            if((abs($propertyStatus) / 1000) < 1) {
                $conflicts[$path] = $property;
            }
        }

        return $conflicts;
    }

    /**
     * gets a certain property
     *
     * @param string $propertyPath
     * @param boolean $throwExceptions throws exception if path elements do not exist
     * @throws Exception
     * @return mixed
     */
    protected function _getProperty($propertyPath, $throwExceptions = false)
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
     * sets a certain bean property
     * 
     * @param string $path
     * @param mixed $value
     * @param boolean $remote flag for remote data
     * @throws MazeLib_View_Bean_Exception
     */
    protected function _setMazeProperty($path, $value, $remote = false)
    {
        $wildcard = $this->_buildWildcardMapping($path);
        $disWildcard = $this->_dissolvePropertyPath($wildcard);
        $disProperty = $this->_dissolvePropertyPath($path);

        $mapping = $this->getMapping($path);
        if(($wildcard && count($disProperty) > count($disWildcard)) || ($mapping && $this->_isMazeProperty($value))) {
            throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be either string, numeric or boolean', array($path)));
        }

        $property = $this;
        foreach ($disProperty as $subProperty) {
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
        if ($mapping) {
            $value = $this->_mergeMazeProperty($path, $property, $value, $remote);
        }

        $property = $value;
    }

    /**
     * sets a certain bean property
     * 
     * @param string $path
     * @param mixed $value
     * @throws MazeLib_View_Bean_Exception
     */
    protected function _setProperty($path, $value)
    {
        $wildcard = $this->_buildWildcardMapping($path);
        if(!$this->_isMazeProperty($value) && ($wildcard || $this->getMapping($path))) {
            throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be maze value', array($path)));
        }

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

        $property = $value;
    }

    /**
     * unmaps value properties in the given array
     *
     * @param array $array
     * @param boolean $remote map maze values to remote
     * @return array
     */
    protected function _unmap(array $array, $remote = false)
    {
        if($this->_isMazeProperty($array)) {
            if ($remote) {
                return $array[self::FIELD_REMOTE_VALUE];
            } else {
                return $array[self::FIELD_LOCAL_VALUE];
            }
        }

        $unmapped = array();
        foreach (array_keys($array) as $arrayKey) {
            $arrayVal = $array[$arrayKey];

            if ($this->_isMazeProperty($arrayVal)) {
                if ($remote) {
                    $arrayVal = $arrayVal[self::FIELD_REMOTE_VALUE];
                } else {
                    $arrayVal = $arrayVal[self::FIELD_LOCAL_VALUE];
                }
            } else if (is_array($arrayVal)) {
                $arrayVal = $this->_unmap($arrayVal, $remote);
            }

            $unmapped[$arrayKey] = $arrayVal;
        }

        return $unmapped;
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
     * @param bool $unmapped
     * @param boolean $remote map maze values to remote
     * @return array
     */
    public function asArray($unmapped = false, $remote = false)
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

        return $this->_unmap($array, $remote);
    }

    /**
     * returns all bean properties in array form and dissolves each bean object
     * in it.
     * 
     * @param boolean $unmapped
     * @param boolean $remote map maze values to remote
     * @return array
     */
    public function asDeepArray($unmapped = false, $remote = false)
    {
        $array = array();

        foreach (get_object_vars($this) as $name => $value) {
            if (mb_strpos($name, self::PREFIX_BEAN_PROPERTIES) === 0) {
                if (($result = $this->$name)) {
                    if ($result instanceof ZendX_AbstractBean) {
                        $result = $result->asDeepArray($unmapped, $remote);
                    } elseif (is_array($result)) {
                        foreach ($result as $key => $value) {
                            if ($value instanceof ZendX_AbstractBean) {
                                $value = $value->asDeepArray($unmapped, $remote);
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

        return $this->_unmap($array, $remote);
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
        if(!$this->searchedConflicts) {
            $this->conflicts = $this->_getConflicts();
            $this->searchedConflicts = true;
        }

        if($status) {
            if(abs($status) / 1000 >= 1) {
                $status = $status / 1000;
            }

            $statusConflicts = array();
            foreach ($this->conflicts as $path => $conflict) {
                if($status === $conflict[self::FIELD_STATUS]) {
                    $statusConflicts[$path] = $conflict;
                }
            }

            return $statusConflicts;
        }

        return $this->conflicts;
    }

    /**
     * gets all properties with local mapping
     *
     * @return array
     */
    public function getData()
    {
        return $this->asDeepArray(false, false);
    }

    /**
     * get status code for a certain maze value by path
     * 
     * @param string $propertyPath
     * @return int|null
     */
    public function getMapping($propertyPath)
    {
        if(array_key_exists($propertyPath, $this->mapping)) {
            return $this->mapping[$propertyPath];
        } elseif(array_key_exists($propertyPath, $this->wildcardMapping)) {
            return $this->wildcardMapping[$propertyPath];
        }

        return null;
    }

    /**
     * gets a certain property with local mapping
     *
     * @param string $path
     * @return mixed
     */
    public function getProperty($path)
    {
        $value = $result = $this->_getProperty($path);

        if (is_array($value)) {
            $result = $this->_resolveArray($value, $path);
        }

        return $result;
    }

    /**
     * gets all data unmapped
     *
     * @return array
     */
    public function getRawData()
    {
        return $this->asDeepArray(true);
    }

    /**
     * gets a certain property unmapped
     *
     * @param string $path
     * @return mixed
     */
    public function getRawProperty($path)
    {
        return $this->_getProperty($path);
    }

    /**
     * gets all data with remote values from maze values
     *
     * @return array
     */
    public function getRemoteData()
    {
        return $this->asDeepArray(false, true);
    }
    
    /**
     * gets a certain property.
     * 
     * @param string $path
     * @return mixed
     */
    public function getRemoteProperty($path)
    {
        $value = $result = $this->_getProperty($path);

        if (is_array($value)) {
            $result = $this->_resolveArray($value, $path, true);
        }
        
        return $result;
    }
    
    /**
     * checks if the given property has a conflict
     * 
     * @param string $propertyPath
     * @param boolean $negative check for negative conflict instead
     * @param boolean $inDepth checks als inDepth from given path
     * @return null|boolean null (default) ignores conflict prefix, boolean value determines if conflict is or is not negative
     */
    public function hasConflict($propertyPath, $negative = null, $inDepth = false)
    {
        if(!($conflicts = $this->getConflicts()) || (!$inDepth && !array_key_exists($propertyPath, $conflicts))) {
            return false;
        }

        if ($inDepth) {
            foreach ($conflicts as $property => $mazeValue) {
                if (($regex = preg_quote($propertyPath, '/')) && !preg_match("/^{$regex}/", $property)) {
                    continue;
                }

                if($negative === true && $mazeValue[self::FIELD_STATUS] >= 1) {
                    continue;
                }else if($negative === false && $mazeValue[self::FIELD_STATUS] < 1) {
                    continue;
                }

                return true;
            }
            return false;
        } else {
            if(!($conflicts = $this->getConflicts()) || !array_key_exists($propertyPath, $conflicts)) {
                return false;
            }

            if($negative === true && $conflicts[$propertyPath][self::FIELD_STATUS] >= 1) {
                return false;
            }

            if($negative === false && $conflicts[$propertyPath][self::FIELD_STATUS] < 1) {
                return false;
            }
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
            $this->_getProperty($propertyPath, true);
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
     * @return void
     */
    public function setBean(array $data)
    {
        $this->setRawData($data);
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
    public function setData(array $data)
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
     * set local property
     * 
     * @param string $path
     * @param mixed $value
     * @return MazeLib_Bean
     * @throws MazeLib_View_Bean_Exception
     */
    public function setProperty($path, $value)
    {
        $this->_reset();

        if(is_array($value) && !$this->_isMazeProperty($value)) {
            return $this->setData($this->_dissolveArray($value, $path));
        }

        $this->_setMazeProperty($path, $value);

        return $this;
    }

    /**
     * Deploys given $data array into bean properties.
     * Uses the key and value pairs.
     *
     * @param array $data
     * @throws MazeLib_View_Bean_Exception
     */
    public function setRawData(array $data)
    {
        $this->_reset();

        foreach ($this->_dissolveArray($data) as $path => $value) {
            if($this->getMapping($path) && !$this->_isMazeProperty($value)) {
                throw new MazeLib_View_Bean_Exception(vsprintf('Bean value for path %1$s must be maze value', array($path)));
            }

            $this->_setProperty($path, $value);
        }
    }

    /**
     * sets a certain bean property
     *
     * @param $path
     * @param mixed $value
     * @return void
     */
    public function setRawProperty($path, $value)
    {
        $this->_reset();

        if(is_array($value) && !$this->_isMazeProperty($value)) {
            return $this->setBean($this->_dissolveArray($value, $path));
        }

        $this->_setProperty($path, $value);
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
    }
    
    /**
     * sets property from remote side
     * 
     * @param string $path
     * @param mixed $value
     * @return void
     * @throws MazeLib_View_Bean_Exception
     */
    public function setRemoteProperty($path, $value)
    {
        $this->_reset();

        if(is_array($value) && !$this->_isMazeProperty($value)) {
            return $this->setRemoteData($this->_dissolveArray($value, $path));
        }

        $this->_setMazeProperty($path, $value, true);
    }
    
    /**
     * unset a certain value bean property.
     * 
     * @param string $propertyPath
     * @return boolean
     */
    public function unsetProperty($propertyPath)
    {
        $disProperty = $this->_dissolvePropertyPath($propertyPath);

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
