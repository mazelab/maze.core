<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_BeanRemoteTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_BeanRemoteTest extends PHPUnit_Framework_TestCase
{

    /**
     *  @var MazeLib_Bean_TestBean
     */
    protected $bean;

    protected function setUp()
    {
        $this->bean = new MazeLib_Bean_TestBean();
    }
    
    public function testSetRemoteDataWithoutMazePathShouldSetValue()
    {
        $data = array(
            'array' => array(
                'key' => 'val'
            )
        );
        
        $this->bean->setRemoteData($data);
        $prop = $this->bean->asArray(true);
        $this->assertEquals($data['array']['key'], $prop['array']['key']['remote']);
    }
    
    public function testSetRemoteDataWithMazePathShouldSetValue()
    {
        $data = array(
            'array/key' => 'test'
        );
        
        $this->bean->setRemoteData($data);
        $prop = $this->bean->asArray(true);
        $this->assertEquals($data['array/key'], $prop['array']['key']['remote']);
    }
    
    public function testSetRemoteDataWithMazePathShouldUse1xCode()
    {
        $data = array(
            'array/key' => 'test'
        );
        
        $this->bean->setRemoteData($data);
        $this->assertEquals(1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetRemoteDataWithMazePathShouldUse2xCode()
    {
        $data = array(
            'maze/val' => 'des'
        );
        
        $this->bean->setRemoteData($data);
        $this->assertEquals(2, $this->bean->getProperty('maze/val/status'));
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetRemoteDataOnMappedPathWithObjectShouldThrowException()
    {
        $data = array(
            'array' => array(
                'key' => new stdClass()
            )
        );
        
        $this->bean->setRemoteData($data);
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetRemoteDataOnMappedPathWithArrayShouldThrowException()
    {
        $data = array(
            'array' => array(
                'key' => array(
                    'key' => 'val'
                )
            )
        );
        
        $this->bean->setRemoteData($data);
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetRemoteDataOnMappedPathWithMazeValueShouldThrowException()
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
        
        $this->bean->setRemoteData($data);
    }
    
    public function testSetRemoteDataWithoutAnyPriorDataShouldSetPositivConflictSate()
    {
        $data = array(
            'array' => array(
                'key' => 'test'
            )
        );
        
        $this->bean->setRemoteData($data);
        
        $this->assertEquals(1, $this->bean->getProperty('array/key/status'));
    }

    public function testSetRemoteDataWithSameLocaleEntryShouldSetSynchedState()
    {
        $data = array(
            'array' => array(
                'key' => 'test'
            )
        );
        
        $this->bean->setLocalData($data);
        $this->bean->setRemoteData($data);
        
        $this->assertEquals(1000, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetRemoteDataWithDifferentLocalEntryShouldSetPositivConflictState()
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
        
        $this->bean->setLocalData($dataLocal);
        $this->bean->setRemoteData($dataRemote);
        
        $this->assertEquals(1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetRemoteDataShouldOverwriteWrongStatus()
    {
        $valueProperty = array(
            'local' => 'nothing',
            'status' => 2000,
            'remote' => 'nothing'
        );
        
        $this->bean->setProperty('array/key', $valueProperty);
        $this->bean->setRemoteData(array('array/key' => 'more'));
        
        $this->assertEquals(1, $this->bean->getProperty('array/key/status'));
    }
    
    public function testGetRemotePropertyWithNormalPathShouldReturnCorrectValue()
    {
        $data = array(
            'path/key' => 'val'
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['path/key'], $this->bean->getRemoteProperty('path/key'));
    }
    
    public function testGetRemotePropertyWithValueBeanPathShouldReturnCorrectRemoteValue()
    {
        $data = array(
            'array/key' => array(
                'local' => 'test',
                'status' => 1,
                'remote' => 'taste'
            )
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['array/key']['remote'], $this->bean->getRemoteProperty('array/key'));
    }
    
    public function testGetRemotePropertyWithRemotePathIntoMazeValueShouldReturnRemoteValue()
    {
        $data = array(
            'array/key' => 'uno'
        );
        
        $this->bean->setRemoteData($data);
        $this->assertEquals('uno', $this->bean->getRemoteProperty('array/key/remote'));
    }

    public function testSetRemotePropertyShouldOverwriteValueFromBefore()
    {
        $this->bean->setRemoteProperty('some/key', 'first string');
        $this->bean->setRemoteProperty('some/key', 'second string');
        $this->assertEquals('second string', $this->bean->getRemoteProperty('some/key'));
    }
    
    public function testSetRemotePropertyOfMazeValueShouldOverwriteValueFromBefore()
    {
        $this->bean->setRemoteProperty('array/key', 'first string');
        $this->bean->setRemoteProperty('array/key', 'second string');
        $this->assertEquals('second string', $this->bean->getRemoteProperty('array/key'));
    }
    
    public function testSetRemotePropertyWithDeepArrayShouldSetValues()
    {
        $data = array(
            'more' => array(
                'keys' => array(
                    1 => 'test'
                )
            )
        );
        
        $this->bean->setRemoteProperty('sample', $data);
        $this->assertEquals($data, $this->bean->getRemoteProperty('sample'));
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetRemotePropertyWithMazeValueInMazePathShouldThrowException()
    {
        $data = array(
            'local' => 'test',
            'status' => 1000,
            'remote' => 'test'
        );
        
        $this->bean->setRemoteProperty('array/key', $data);
    }
    
    public function testSetRemotePropertyWithMazeValueInNonMazePathShouldSetMazeValue()
    {
        $data = array(
            'local' => 'test',
            'status' => 1000,
            'remote' => 'test'
        );
        
        $this->bean->setRemoteProperty('some/key', $data);
        $this->assertEquals($data, $this->bean->getRemoteProperty('some/key'));
    }
    
    public function testSetRemtoePropertyShouldCreateLocalValueFieldWithNull()
    {
        $result = array(
            'local' => null,
            'status' => 1,
            'remote' => 'test'
        );
        
        $this->bean->setRemoteProperty('array/key', $result['remote']);
        $this->assertEquals($result, $this->bean->getProperty('array/key'));
    }
    
    public function testSetRemotePropertyWithBooleanShouldSetBoolean()
    {
        $this->bean->setRemoteProperty('array/key', true);
        $this->assertTrue($this->bean->getRemoteProperty('array/key'));
    }
    
    public function testHasConflictWithOnlyRemotePropertyShouldReturnTrue()
    {
        $this->bean->setRemoteProperty('array/key', 'val');
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }

    public function testHasConflictWhenOnlyRemotePropertyIsFalseShouldReturnTrue()
    {
        $this->bean->setRemoteProperty('array/key', false);
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWhenOnlyRemotePropertyIsNullShouldReturnFalse()
    {
        $this->bean->setRemoteProperty('array/key', null);
        $this->assertFalse($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWithNegativeAfterRemoteSetShouldReturnFalse()
    {
        $this->bean->setRemoteProperty('array/key', 'conflict');
        
        $this->assertFalse($this->bean->hasConflict('array/key', true));
    }
    
    public function testHasConflictAfterRemoteSetWithShouldReturnTrue()
    {
        $this->bean->setRemoteProperty('array/key', 'conflict');
        
        $this->assertTrue($this->bean->hasConflict('array/key'));
    }
    
    public function testGetConflictsAfterRemoteSetShouldReturnConflicts()
    {
        $data = array(
            'array/key' => 'uno',
        );
        
        $conflicts = array(
            'array/key' => array(
                'local' => null,
                'status' => 1,
                'remote' => 'uno'
            )
        );
        
        $this->bean->setRemoteData($data);
        $this->assertEquals($conflicts, $this->bean->getConflicts());
    }
    
    public function testGetConflictsAfterRemoteSetWithNegativ1xStatusShouldEmptyArray()
    {
        $this->bean->setRemoteProperty('array/key', 'conflict');
        
        $this->assertEmpty($this->bean->getConflicts(-1));
    }
    
    public function testGetConflictsAfterRemoteSetWithPositiv1xStatusShouldOneEntry()
    {
        $this->bean->setRemoteProperty('array/key', 'conflict');
        
        $this->assertCount(1, $this->bean->getConflicts(1));
    }
    
    public function testGetRemoteDataOnEmptyBeanShouldReturnEmtpyArray()
    {
        $this->assertInternalType('array', $this->bean->getRemoteData());
        $this->assertEmpty($this->bean->getRemoteData());
    }
    
    public function testGetRemoteDataShouldReturn2Entries()
    {
        $this->bean->setRemoteProperty('array/key', 'ichi');
        $this->bean->setRemoteProperty('maze/val', 'ni');
        
        $this->assertCount(2, $this->bean->getRemoteData());
    }
    
    public function testGetRemoteDataShouldReturnCorrectArrayStruct()
    {
        $this->bean->setRemoteProperty('array/key', 'ichi');
        $this->bean->setRemoteProperty('maze/val', 'ni');

        $remoteData = $this->bean->getRemoteData();
        $this->assertArrayHasKey('array', $remoteData);
        $this->assertArrayHasKey('maze', $remoteData);

        $this->assertArrayHasKey('key', $remoteData['array']);
        $this->assertArrayHasKey('val', $remoteData['maze']);
    }
    
    public function testGetRemoteDataShouldReturnCorrectValues()
    {
        $this->bean->setRemoteProperty('array/key', 'ichi');
        $this->bean->setRemoteProperty('maze/val', 'ni');

        $remoteData = $this->bean->getRemoteData();
        
        $this->assertEquals('ichi', $remoteData['array']['key']);
        $this->assertEquals('ni', $remoteData['maze']['val']);
    }
    
    public function testSetRemotePropertyAsWildcardShouldSetMazeValue()
    {
        $value = 'testing';
        $result = array(
            'local' => null,
            'status' => 2,
            'remote' => $value
        );
        
        $this->bean->setRemoteProperty('wildcard/test1', $value);
        $this->assertEquals($result, $this->bean->getProperty('wildcard/test1'));
    }
    
    public function testSetRemoteDataOfWildcardEntriesShouldSetValues()
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
                    'local' => null,
                    'status' => 2,
                    'remote' => 'test1'
                ),
                'key2' => array(
                    'local' => null,
                    'status' => 2,
                    'remote' => 'test2'                    
                )
            )
        );
        
        $this->bean->setRemoteData($data);
        
        $this->assertEquals($result, $this->bean->asArray(true));
    }
    
    public function testGetRemoteDataShouldReturnWildcardEntries()
    {
        $data = array(
            'wildcard' => array(
                'key1' => 'test1',
                'key2' => 'test2'
            )
        );
        
        $this->bean->setRemoteData($data);
        
        $this->assertEquals($data, $this->bean->getRemoteData());
    }
    
    public function testSetRemotePropertyInConflictedMazeValueWithSameLocalPropertyShouldNotChangeConflictState()
    {
        $this->bean->setRemoteProperty('array/key', 'taste');
        $this->bean->setLocalProperty('array/key', 'test');
        $this->bean->setRemoteProperty('array/key', 'taste');
        
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
    }

    public function testGetRemotePropertyOfWildcardEntryShouldReturnCorrectString()
    {
        $this->bean->setRemoteProperty('wildcard/1', 'test');
        
        $this->assertEquals('test', $this->bean->getRemoteProperty('wildcard/1'));
    }
    
    public function testGetRemotePropertyOfWildcardParentShouldOnlyReturnLocalValues()
    {
        $data = array(
            '1' => 'test'
        );
        
        $this->bean->setRemoteProperty('wildcard/1', 'test');
        $this->assertEquals($data, $this->bean->getRemoteProperty('wildcard'));
    }
    
}

