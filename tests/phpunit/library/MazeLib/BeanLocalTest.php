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
        
        $this->bean->setData($data);
        $this->assertEquals($data['array']['key'], $this->bean->getProperty('array/key'));
    }
    
    public function testSetLocalDataWithMazePathShouldSetValue()
    {
        $data = array(
            'array/key' => 'test'
        );
        
        $this->bean->setData($data);
        $this->assertEquals($data['array/key'], $this->bean->getProperty('array/key'));
    }
    
    public function testSetLocalDataWithMazePathShouldUse1xCode()
    {
        $data = array(
            'array/key' => 'test'
        );
        
        $this->bean->setData($data);
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetLocalDataWithMazePathShouldUse2xCode()
    {
        $data = array(
            'maze/val' => 'des'
        );
        
        $this->bean->setData($data);
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
        
        $this->bean->setData($data);
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
        
        $this->bean->setData($data);
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
        
        $this->bean->setData($data);
    }
    
    public function testSetLocalDataWithoutAnyPriorDataShouldSetNegativConflictSate()
    {
        $data = array(
            'array' => array(
                'key' => 'test'
            )
        );
        
        $this->bean->setData($data);
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
        $this->bean->setData($data);
        
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
        $this->bean->setData($dataLocal);
        
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetLocalDataShouldOverwriteWrongStatus()
    {
        $valueProperty = array(
            'local' => 'nothing',
            'status' => 2000,
            'remote' => 'nothing'
        );
        
        $this->bean->setRawProperty('array/key', $valueProperty);
        $this->bean->setData(array('array/key' => 'nothing'));
        
        $this->assertEquals(1000, $this->bean->getProperty('array/key/status'));
    }
    
    public function testGetLocalPropertyWithNormalPathShouldReturnCorrectValue()
    {
        $data = array(
            'path/key' => 'val'
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['path/key'], $this->bean->getProperty('path/key'));
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
        $this->assertEquals($data['array/key']['local'], $this->bean->getProperty('array/key'));
    }
    
    public function testGetLocalPropertyWithLocalPathIntoMazeValueShouldReturnLocalValue()
    {
        $data = array(
            'array/key' => 'uno'
        );
        
        $this->bean->setData($data);
        $this->assertEquals('uno', $this->bean->getProperty('array/key/local'));
    }
    
    public function testSetPropertyShouldOverwriteValueFromBefore()
    {
        $this->bean->setProperty('some/key', 'first string');
        $this->bean->setProperty('some/key', 'second string');
        $this->assertEquals('second string', $this->bean->getProperty('some/key'));
    }
    
    public function testSetPropertyOfMazeValueShouldOverwriteValueFromBefore()
    {
        $this->bean->setProperty('array/key', 'first string');
        $this->bean->setProperty('array/key', 'second string');
        $this->assertEquals('second string', $this->bean->getProperty('array/key'));
    }
    
    public function testSetPropertyWithDeepArrayShouldSetValues()
    {
        $data = array(
            'more' => array(
                'keys' => array(
                    1 => 'test'
                )
            )
        );
        
        $this->bean->setProperty('sample', $data);
        $this->assertEquals($data, $this->bean->getProperty('sample'));
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetPropertyWithMazeValueInMazePathShouldThrowException()
    {
        $data = array(
            'local' => 'test',
            'status' => 1000,
            'remote' => 'test'
        );
        
        $this->bean->setProperty('array/key', $data);
    }
    
    public function testSetPropertyWithMazeValueInNonMazePathShouldSetMazeValue()
    {
        $data = array(
            'local' => 'test',
            'status' => 1000,
            'remote' => 'test'
        );
        
        $this->bean->setProperty('some/key', $data);
        $this->assertEquals($data, $this->bean->getProperty('some/key'));
    }
    
    public function testSetPropertyShouldCreateRemoteValueFieldWithNull()
    {
        $result = array(
            'local' => 'test',
            'status' => -1,
            'remote' => null
        );
        
        $this->bean->setProperty('array/key', $result['local']);
        $this->assertEquals($result, $this->bean->getRawProperty('array/key'));
    }
    
    public function testSetPropertyWithBooleanShouldSetBoolean()
    {
        $this->bean->setProperty('array/key', true);
        $this->assertTrue($this->bean->getProperty('array/key'));
    }
    
    public function testHasConflictWithOnlyLocalPropertyShouldReturnTrue()
    {
        $this->bean->setProperty('array/key', 'val');
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWhenOnlyLocalPropertyIsFalseShouldReturnTrue()
    {
        $this->bean->setProperty('array/key', false);
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWhenOnlyLocalPropertyIsNullShouldReturnFalse()
    {
        $this->bean->setProperty('array/key', null);
        $this->assertFalse($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictAfterLocalSetWithShouldReturnTrue()
    {
        $this->bean->setProperty('array/key', 'conflict');
        
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictNegativeAfterLocalSetShouldReturnTrue()
    {
        $this->bean->setProperty('array/key', 'conflict');
        
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
        
        $this->bean->setData($data);
        $this->assertEquals($conflicts, $this->bean->getConflicts());
    }
    
    public function testGetConflictsAfterLocalSetWithNegativ1xStatusShouldReturnOneEntry()
    {
        $this->bean->setProperty('array/key', 'conflict');
        
        $this->assertCount(1, $this->bean->getConflicts(-1));
    }
    
    public function testGetConflictsAfterLocalSetWithPositiv1xStatusShouldReturnEmptyArray()
    {
        $this->bean->setProperty('array/key', 'conflict');
        
        $this->assertEmpty($this->bean->getConflicts(1));
    }
    
    public function testGetLocalDataOnEmptyBeanShouldReturnEmtpyArray()
    {
        $this->assertInternalType('array', $this->bean->getData());
        $this->assertEmpty($this->bean->getData());
    }
    
    public function testGetLocalDataShouldReturn2Entries()
    {
        $this->bean->setProperty('array/key', 'ichi');
        $this->bean->setProperty('maze/val', 'ni');
        
        $this->assertCount(2, $this->bean->getData());
    }
    
    public function testGetLocalDataShouldReturnCorrectArrayStruct()
    {
        $this->bean->setProperty('array/key', 'ichi');
        $this->bean->setProperty('maze/val', 'ni');

        $localData = $this->bean->getData();
        $this->assertArrayHasKey('array', $localData);
        $this->assertArrayHasKey('maze', $localData);

        $this->assertArrayHasKey('key', $localData['array']);
        $this->assertArrayHasKey('val', $localData['maze']);
    }
    
    public function testGetLocalDataShouldReturnCorrectValues()
    {
        $this->bean->setProperty('array/key', 'ichi');
        $this->bean->setProperty('maze/val', 'ni');

        $localData = $this->bean->getData();
        
        $this->assertEquals('ichi', $localData['array']['key']);
        $this->assertEquals('ni', $localData['maze']['val']);
    }
    
    public function testSetPropertyAsWildcardShouldSetMazeValue()
    {
        $value = 'testing';
        $result = array(
            'local' => $value,
            'status' => -2,
            'remote' => null
        );
        
        $this->bean->setProperty('wildcard/test1', $value);
        $this->assertEquals($result, $this->bean->getRawProperty('wildcard/test1'));
    }
    
    public function testGetLocalPropertyAsWildcardShouldSetMazeValue()
    {
        $value = 'testing';
        $result = array(
            'local' => $value,
            'status' => -2,
            'remote' => null
        );
        
        $this->bean->setProperty('wildcard/test1', $value);
        $this->assertEquals($result, $this->bean->getRawProperty('wildcard/test1'));
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
        
        $this->bean->setData($data);
        
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
        
        $this->bean->setData($data);
        
        $this->assertEquals($data, $this->bean->getData());
    }

    public function testSetPropertyInConflictedMazeValueWithSameLocalPropertyShouldOverwriteChangeConflictState()
    {
        $this->bean->setProperty('array/key', 'test');
        $this->bean->setRemoteProperty('array/key', 'taste');
        $this->bean->setProperty('array/key', 'test');
        
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }

    public function testGetLocalPropertyOfWildcardEntryShouldReturnCorrectString()
    {
        $this->bean->setProperty('wildcard/1', 'test');
        
        $this->assertEquals('test', $this->bean->getProperty('wildcard/1'));
    }
    
    public function testGetLocalPropertyOfWildcardParentShouldOnlyReturnLocalValues()
    {
        $data = array(
            '1' => 'test'
        );
        
        $this->bean->setProperty('wildcard/1', 'test');
        $this->assertEquals($data, $this->bean->getProperty('wildcard'));
    }
    
}

