<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_BeanLocalTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_BeanLocalTest extends PHPUnit_Framework_TestCase
{

    /**
     *  @var MazeLib_Bean_TestBean
     */
    protected $bean;

    protected function setUp()
    {
        $this->bean = new MazeLib_Bean_TestBean();
    }
    
    public function testSetLocalDataWithoutMazePathShouldSetValue()
    {
        $data = array(
            'array' => array(
                'key' => 'val'
            )
        );
        
        $this->bean->setLocalData($data);
        $this->assertEquals($data['array']['key'], $this->bean->getLocalProperty('array/key'));
    }
    
    public function testSetLocalDataWithMazePathShouldSetValue()
    {
        $data = array(
            'array/key' => 'test'
        );
        
        $this->bean->setLocalData($data);
        $this->assertEquals($data['array/key'], $this->bean->getLocalProperty('array/key'));
    }
    
    public function testSetLocalDataWithMazePathShouldUse1xCode()
    {
        $data = array(
            'array/key' => 'test'
        );
        
        $this->bean->setLocalData($data);
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetLocalDataWithMazePathShouldUse2xCode()
    {
        $data = array(
            'maze/val' => 'des'
        );
        
        $this->bean->setLocalData($data);
        $this->assertEquals(-2, $this->bean->getProperty('maze/val/status'));
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetLocalDataOnMappedPathWithObjectShouldThrowException()
    {
        $data = array(
            'array' => array(
                'key' => new stdClass()
            )
        );
        
        $this->bean->setLocalData($data);
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetLocalDataOnMappedPathWithArrayShouldThrowException()
    {
        $data = array(
            'array' => array(
                'key' => array(
                    'key' => 'val'
                )
            )
        );
        
        $this->bean->setLocalData($data);
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetLocalDataOnMappedPathWithMazeValueShouldThrowException()
    {
        $data = array(
            'array' => array(
                'key' => array(
                    'local' => 'test',
                    'status' => 1000,
                    'remote' => 'test'
                )
            )
        );
        
        $this->bean->setLocalData($data);
    }
    
    public function testSetLocalDataWithoutAnyPriorDataShouldSetNegativConflictSate()
    {
        $data = array(
            'array' => array(
                'key' => 'test'
            )
        );
        
        $this->bean->setLocalData($data);
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetLocalDataWithSameRemoteEntryShouldSetSynchedState()
    {
        $data = array(
            'array' => array(
                'key' => 'test'
            )
        );
        
        $this->bean->setRemoteData($data);
        $this->bean->setLocalData($data);
        
        $this->assertEquals(1000, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetLocalDataWithDifferentRemoteEntryShouldSetNegativConflictState()
    {
        $dataRemote = array(
            'array' => array(
                'key' => 't1'
            )
        );
        $dataLocal = array(
            'array' => array(
                'key' => 'test'
            )
        );
        
        $this->bean->setRemoteData($dataRemote);
        $this->bean->setLocalData($dataLocal);
        
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetLocalDataShouldOverwriteWrongStatus()
    {
        $valueProperty = array(
            'local' => 'nothing',
            'status' => 2000,
            'remote' => 'nothing'
        );
        
        $this->bean->setProperty('array/key', $valueProperty);
        $this->bean->setLocalData(array('array/key' => 'nothing'));
        
        $this->assertEquals(1000, $this->bean->getProperty('array/key/status'));
    }
    
    public function testGetLocalPropertyWithNormalPathShouldReturnCorrectValue()
    {
        $data = array(
            'path/key' => 'val'
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['path/key'], $this->bean->getLocalProperty('path/key'));
    }
    
    public function testGetLocalPropertyWithValueBeanPathShouldReturnCorrectLocalValue()
    {
        $data = array(
            'array/key' => array(
                'local' => 'test',
                'status' => -1,
                'remote' => 'taste'
            )
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['array/key']['local'], $this->bean->getLocalProperty('array/key'));
    }
    
    public function testGetLocalPropertyWithLocalPathIntoMazeValueShouldReturnLocalValue()
    {
        $data = array(
            'array/key' => 'uno'
        );
        
        $this->bean->setLocalData($data);
        $this->assertEquals('uno', $this->bean->getLocalProperty('array/key/local'));
    }
    
    public function testSetLocalPropertyShouldOverwriteValueFromBefore()
    {
        $this->bean->setLocalProperty('some/key', 'first string');
        $this->bean->setLocalProperty('some/key', 'second string');
        $this->assertEquals('second string', $this->bean->getLocalProperty('some/key'));
    }
    
    public function testSetLocalPropertyOfMazeValueShouldOverwriteValueFromBefore()
    {
        $this->bean->setLocalProperty('array/key', 'first string');
        $this->bean->setLocalProperty('array/key', 'second string');
        $this->assertEquals('second string', $this->bean->getLocalProperty('array/key'));
    }
    
    public function testSetLocalPropertyWithDeepArrayShouldSetValues()
    {
        $data = array(
            'more' => array(
                'keys' => array(
                    1 => 'test'
                )
            )
        );
        
        $this->bean->setLocalProperty('sample', $data);
        $this->assertEquals($data, $this->bean->getLocalProperty('sample'));
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetLocalPropertyWithMazeValueInMazePathShouldThrowException()
    {
        $data = array(
            'local' => 'test',
            'status' => 1000,
            'remote' => 'test'
        );
        
        $this->bean->setLocalProperty('array/key', $data);
    }
    
    public function testSetLocalPropertyWithMazeValueInNonMazePathShouldSetMazeValue()
    {
        $data = array(
            'local' => 'test',
            'status' => 1000,
            'remote' => 'test'
        );
        
        $this->bean->setLocalProperty('some/key', $data);
        $this->assertEquals($data, $this->bean->getLocalProperty('some/key'));
    }
    
    public function testSetLocalPropertyShouldCreateRemoteValueFieldWithNull()
    {
        $result = array(
            'local' => 'test',
            'status' => -1,
            'remote' => null
        );
        
        $this->bean->setLocalProperty('array/key', $result['local']);
        $this->assertEquals($result, $this->bean->getProperty('array/key'));
    }
    
    public function testSetLocalPropertyWithBooleanShouldSetBoolean()
    {
        $this->bean->setLocalProperty('array/key', true);
        $this->assertTrue($this->bean->getLocalProperty('array/key'));
    }
    
    public function testHasConflictWithOnlyLocalPropertyShouldReturnTrue()
    {
        $this->bean->setLocalProperty('array/key', 'val');
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWhenOnlyLocalPropertyIsFalseShouldReturnTrue()
    {
        $this->bean->setLocalProperty('array/key', false);
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWhenOnlyLocalPropertyIsNullShouldReturnFalse()
    {
        $this->bean->setLocalProperty('array/key', null);
        $this->assertFalse($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictAfterLocalSetWithShouldReturnTrue()
    {
        $this->bean->setLocalProperty('array/key', 'conflict');
        
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictNegativeAfterLocalSetShouldReturnTrue()
    {
        $this->bean->setLocalProperty('array/key', 'conflict');
        
        $this->assertTrue($this->bean->hasConflict('array/key', true));
    }
    
    public function testGetConflictsAfterLocalSetShouldReturnConflicts()
    {
        $data = array(
            'array/key' => 'uno',
        );
        
        $conflicts = array(
            'array/key' => array(
                'local' => 'uno',
                'status' => -1,
                'remote' => null
            )
        );
        
        $this->bean->setLocalData($data);
        $this->assertEquals($conflicts, $this->bean->getConflicts());
    }
    
    public function testGetConflictsAfterLocalSetWithNegativ1xStatusShouldReturnOneEntry()
    {
        $this->bean->setLocalProperty('array/key', 'conflict');
        
        $this->assertCount(1, $this->bean->getConflicts(-1));
    }
    
    public function testGetConflictsAfterLocalSetWithPositiv1xStatusShouldReturnEmptyArray()
    {
        $this->bean->setLocalProperty('array/key', 'conflict');
        
        $this->assertEmpty($this->bean->getConflicts(1));
    }
    
    public function testGetLocalDataOnEmptyBeanShouldReturnEmtpyArray()
    {
        $this->assertInternalType('array', $this->bean->getLocalData());
        $this->assertEmpty($this->bean->getLocalData());
    }
    
    public function testGetLocalDataShouldReturn2Entries()
    {
        $this->bean->setLocalProperty('array/key', 'ichi');
        $this->bean->setLocalProperty('maze/val', 'ni');
        
        $this->assertCount(2, $this->bean->getLocalData());
    }
    
    public function testGetLocalDataShouldReturnCorrectArrayStruct()
    {
        $this->bean->setLocalProperty('array/key', 'ichi');
        $this->bean->setLocalProperty('maze/val', 'ni');

        $localData = $this->bean->getLocalData();
        $this->assertArrayHasKey('array', $localData);
        $this->assertArrayHasKey('maze', $localData);

        $this->assertArrayHasKey('key', $localData['array']);
        $this->assertArrayHasKey('val', $localData['maze']);
    }
    
    public function testGetLocalDataShouldReturnCorrectValues()
    {
        $this->bean->setLocalProperty('array/key', 'ichi');
        $this->bean->setLocalProperty('maze/val', 'ni');

        $localData = $this->bean->getLocalData();
        
        $this->assertEquals('ichi', $localData['array']['key']);
        $this->assertEquals('ni', $localData['maze']['val']);
    }
    
    public function testSetLocalPropertyAsWildcardShouldSetMazeValue()
    {
        $value = 'testing';
        $result = array(
            'local' => $value,
            'status' => -2,
            'remote' => null
        );
        
        $this->bean->setLocalProperty('wildcard/test1', $value);
        $this->assertEquals($result, $this->bean->getProperty('wildcard/test1'));
    }
    
    public function testGetLocalPropertyAsWildcardShouldSetMazeValue()
    {
        $value = 'testing';
        $result = array(
            'local' => $value,
            'status' => -2,
            'remote' => null
        );
        
        $this->bean->setLocalProperty('wildcard/test1', $value);
        $this->assertEquals($result, $this->bean->getProperty('wildcard/test1'));
    }
    
    public function testSetLocalDataOfWildcardEntriesShouldSetValues()
    {
        $data = array(
            'wildcard' => array(
                'key1' => 'test1',
                'key2' => 'test2'
            )
        );
        
        $result = array(
            'wildcard' => array(
                'key1' => array(
                    'local' => 'test1',
                    'status' => -2,
                    'remote' => null
                ),
                'key2' => array(
                    'local' => 'test2',
                    'status' => -2,
                    'remote' => null
                )
            )
        );
        
        $this->bean->setLocalData($data);
        
        $this->assertEquals($result, $this->bean->asArray(true));
    }
    
    public function testGetLocalDataShouldReturnWildcardEntries()
    {
        $data = array(
            'wildcard' => array(
                'key1' => 'test1',
                'key2' => 'test2'
            )
        );
        
        $this->bean->setLocalData($data);
        
        $this->assertEquals($data, $this->bean->getLocalData());
    }

    public function testSetLocalPropertyInConflictedMazeValueWithSameLocalPropertyShouldOverwriteChangeConflictState()
    {
        $this->bean->setLocalProperty('array/key', 'test');
        $this->bean->setRemoteProperty('array/key', 'taste');
        $this->bean->setLocalProperty('array/key', 'test');
        
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }

    public function testGetLocalPropertyOfWildcardEntryShouldReturnCorrectString()
    {
        $this->bean->setLocalProperty('wildcard/1', 'test');
        
        $this->assertEquals('test', $this->bean->getLocalProperty('wildcard/1'));
    }
    
    public function testGetLocalPropertyOfWildcardParentShouldOnlyReturnLocalValues()
    {
        $data = array(
            '1' => 'test'
        );
        
        $this->bean->setLocalProperty('wildcard/1', 'test');
        $this->assertEquals($data, $this->bean->getLocalProperty('wildcard'));
    }
    
}

