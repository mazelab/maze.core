<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_BeanWildcardTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_BeanWildcardTest extends PHPUnit_Framework_TestCase
{

    /**
     *  @var MazeLib_Bean_TestBean
     */
    protected $bean;

    protected function setUp()
    {
        $this->bean = new MazeLib_Bean_TestBean();
    }
    
    public function testSetPropertyWithLocalValueAsWildcardShouldSetMazeValue()
    {
        $data = array(
            'local' => 'testing',
            'status' => -2,
            'remote' => null
        );
        
        $this->bean->setProperty('wildcard/test1', $data);
        $this->assertEquals($data, $this->bean->getProperty('wildcard/test1'));
    }
    
    public function testSetLocalAndRemotePropertyOnWildcardFieldShouldSetSynchedStatus()
    {
        $this->bean->setLocalProperty('wildcard/test1', 'test');
        $this->bean->setRemoteProperty('wildcard/test1', 'test');
        $this->assertEquals(2000, $this->bean->getProperty('wildcard/test1/status'));
    }
    
    public function testSetLocalAndRemoteOnWildcardShouldSetPositivErrorStatus()
    {
        $this->bean->setLocalProperty('wildcard/test1', 'testing');
        $this->bean->setRemoteProperty('wildcard/test1', 'testing1');
        $this->assertEquals(2, $this->bean->getProperty('wildcard/test1/status'));
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetPropertyWithArrayIntoWildcardShouldThrowException()
    {
        $value = array(
            'deep' => array(
                'struct' => 'test1'
            )
        );
        
        $this->bean->setProperty('wildcard/test1', $value);
    }
    
    public function testSetBeanOfWildcardEntriesShouldSetValues()
    {
        $data = array(
            'wildcard' => array(
                'key1' => array(
                    'local' => 'test1',
                    'status' => 2000,
                    'remote' => 'test1'
                ),
                'key2' => array(
                    'local' => 'test2',
                    'status' => 2000,
                    'remote' => 'test2'                    
                )
            )
        );
        
        $this->bean->setBean($data);
        
        $this->assertEquals($data, $this->bean->asArray(true));
    }
    
    public function testSetPropertyOfPropertyBeforeWildCardShouldSetString()
    {
        $this->bean->setProperty('wildcard', 'testing');
        $this->assertEquals('testing', $this->bean->getProperty('wildcard'));
    }
    
    public function testSetPropertyOnWildCardOnShouldOverwriteProperty()
    {
        $data = array(
            'local' => 'test',
            'status' => 2000,
            'remote' => 'test'
        );
        
        $data2 = array(
            'local' => 'taste',
            'status' => -2,
            'remote' => 'test'
        );
        
        $this->bean->setProperty('wildcard/test1', $data);
        $this->bean->setProperty('wildcard/test1', $data2);
        
        $this->assertEquals($data2, $this->bean->getProperty('wildcard/test1'));
    }
    
    public function testSetPropertyWithFirstTierWildCardShouldSetMappedProperties()
    {
        $valueBean = new MazeLib_Bean_TestWildcardBean();
        
        $value1 = 'test1';
        $value2 = 'test2';
        $result = array(
            'prop1' => array(
                'local' => $value1,
                'status' => -1,
                'remote' => null
            ),
            'prop2' => array(
                'local' => null,
                'status' => 1,
                'remote' => $value2
            )
        );
        
        $valueBean->setLocalProperty('prop1', $value1);
        $valueBean->setRemoteProperty('prop2', $value2);
        
        $this->assertEquals($result, $valueBean->asDeepArray(true));
    }

    public function testAsArrayShouldMapLocalWildcardMazeValuesProperly()
    {
        $data = array(
            'wildcard' => array(
                '1' => 'test'
            )
        );
        
        $this->bean->setLocalProperty('wildcard/1', 'test');
        
        $this->assertEquals($data, $this->bean->asArray());
    }
    
    public function testAsDeepArrayShouldMapLocalWildcardMazeValueProperly()
    {
        $data = array(
            'wildcard' => array(
                '1' => 'test',
                '2' => 'taste'
            )
        );
        
        $this->bean->setLocalProperty('wildcard/1', 'test');
        $this->bean->setLocalProperty('wildcard/2', 'taste');
        
        $this->assertEquals($data, $this->bean->asDeepArray());
    }
    
}

