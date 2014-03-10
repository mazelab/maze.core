<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_BeanCoreTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_BeanCoreTest extends PHPUnit_Framework_TestCase
{
    
    /**
     *  @var MazeLib_Bean_TestBean
     */
    protected $bean;
    
    /**
     *  @var MazeLib_Bean
     */
    protected $testBean;

    protected function setUp()
    {
        $propertyMap = array(
            "foo" => "one",
            "bar" => "two",
            "baz" => new MazeLib_Bean_TestSubBean(array("abc" => "first", "def" => "second")),
            "quux" => array("ichi", "ni", "san", "shi", new MazeLib_Bean_TestSubBean(array("abc" => "erster", "def" => "zweiter"))),
            "quuux" => array("null" => "zero", "eins" => "one", "zwei" => "two", "drei" => new MazeLib_Bean_TestSubBean(array("abc" => "primus", "def" => "secundus")))
        );
        
        $this->testBean = new MazeLib_Bean_TestBean($propertyMap);
        $this->bean = new MazeLib_Bean_TestBean();
    }
    
    public function testSimpleValueSeter()
    {
        $array = array(
            'uno' => 'dos',
            'tres' => 'quadro'
        );
        $valueBean = new MazeLib_Bean_TestBean();

        $valueBean->setBean($array);
        $this->assertEquals($array, $valueBean->asArray());
    }
    
    public function testHasProperty()
    {
        $this->assertTrue($this->testBean->hasProperty("foo"));

        $this->testBean->setProperty("foo", NULL);
        $this->assertTrue($this->testBean->hasProperty("foo"));

        $this->assertFalse($this->testBean->hasProperty("undefined"));
    }

    public function testGetProperty()
    {
        $this->assertEquals("one", $this->testBean->getProperty("foo"));
        $this->assertEquals("two", $this->testBean->getProperty("bar"));
    }

    public function testSetGetProperty()
    {
        $this->testBean->setProperty("foo", "neuer Wert");
        $this->assertEquals("neuer Wert", $this->testBean->getProperty("foo"));
    }

    public function testNoExceptionWhenObjectWithUndefinedKeyIsCreated()
    {
        $test = new MazeLib_Bean_TestBean(array("undefined" => "value"));
    }

    public function testNoExceptionWhenUndefinedPropertyIsSet()
    {
        $test = new MazeLib_Bean_TestBean();

        $test->setProperty("undefined", null);
    }

    public function testReturnOfNullValueWhenUndefinedPropertyIsRead() 
    {
        $test = new MazeLib_Bean_TestBean();

        $this->assertNull($test->getProperty("undefined"));
    }

    public function testAsArrayWithSubObject() 
    {
        $resultingArray = $this->testBean->asArray();

        $this->assertEquals("one", $resultingArray["foo"]);
        $this->assertEquals("two", $resultingArray["bar"]);
    }

    public function testAsArrayWithSubObjectArray() 
    {
        $resultingArray = $this->testBean->asArray();

        $this->assertEquals("one", $resultingArray["foo"]);
        $this->assertEquals("two", $resultingArray["bar"]);
        $this->assertTrue($resultingArray["baz"] instanceof MazeLib_Bean_TestSubBean);
        $this->assertTrue($resultingArray["quux"][4] instanceof MazeLib_Bean_TestSubBean);
        $this->assertTrue($resultingArray["quuux"]["drei"] instanceof MazeLib_Bean_TestSubBean);
    }

    public function testGetPropertyWithSubObject() 
    {
        $this->assertEquals("one", $this->testBean->getProperty("foo"));
        $this->assertEquals("first", $this->testBean->getProperty("baz/abc"));
        $this->assertEquals("ichi", $this->testBean->getProperty("quux/0"));
        $this->assertEquals("erster", $this->testBean->getProperty("quux/4/abc"));
        $this->assertEquals("zero", $this->testBean->getProperty("quuux/null"));
        $this->assertEquals("primus", $this->testBean->getProperty("quuux/drei/abc"));
    }

    public function testGetPropertyWithSubObjectAsDeepArray() 
    {
        $resultingArray = $this->testBean->asDeepArray();

        $this->assertEquals('one', $resultingArray['foo']);
        $this->assertEquals('first', $resultingArray['baz']['abc']);
        $this->assertEquals('ichi', $resultingArray['quux'][0]);
        $this->assertEquals('erster', $resultingArray['quux'][4]['abc']);
        $this->assertEquals('zero', $resultingArray['quuux']['null']);
        $this->assertEquals('primus', $resultingArray['quuux']['drei']['abc']);
    }

    public function testGetPropertyWithNullProperty() 
    {
        $testBean = new MazeLib_Bean_TestBean(array('one' => array('two' => NULL)));

        $this->assertNull($testBean->getProperty('one/two/three/four'));
    }

    public function testAdditionOfArrayProperties() 
    {
        $this->testBean->setProperty("quuux/test", "neuer Wert");
        $this->assertEquals("neuer Wert", $this->testBean->getProperty("quuux/test"));

        $this->testBean->setProperty("quuux/deepTest/one/two/three/test", "zweiter neuer Wert");
        $this->assertEquals("zweiter neuer Wert", $this->testBean->getProperty("quuux/deepTest/one/two/three/test"));
        
        
        $this->testBean->setProperty("quuux/7", "dritter neuer Wert");
        $this->assertEquals("dritter neuer Wert", $this->testBean->getProperty("quuux/7"));
    }
    
    public function testSetValueWithIndizeInEmptyBean()
    {
        $this->testBean = new MazeLib_Bean_TestBean();
        
        $this->testBean->setProperty("quuux/0", "modern walking");
        $this->assertEquals("modern walking", $this->testBean->getProperty("quuux/0"));
        
        $this->testBean->setProperty("quuux/test1", "ganz neuer Wert");
        $this->assertEquals("ganz neuer Wert", $this->testBean->getProperty("quuux/test1"));
        
        $this->testBean->setProperty("quuuux/1/3", "triplet");
        $this->assertEquals("triplet", $this->testBean->getProperty("quuuux/1/3"));
    }
    
    public function testSetValueWithIndizeInEmptyObject()
    {
        $this->testBean = new MazeLib_Bean_TestBean();
        
        $this->testBean->setProperty("quuux/0", new stdClass());
        $this->assertEquals(new stdClass(), $this->testBean->getProperty("quuux/0"));
        
        $this->testBean->setProperty("quuux/0/1", "trials");
        $this->assertEquals("trials", $this->testBean->getProperty("quuux/0/1"));
        
        $this->testBean->setProperty("quuux/0/2/3", "memorizing");
        $this->assertEquals("memorizing", $this->testBean->getProperty("quuux/0/2/3"));
    }

    public function testAdditionOfStdObjectProperties() 
    {
        $array = array(
            "alpha" => array("eins", "zwei", "drei"),
            "beta" => new MazeLib_Bean_TestBean());

        $object = (object) $array;

        $this->testBean->setProperty("quuux/test", $object);
        $this->testBean->setProperty("quuux/test/beta/foo", "foobar");
        $this->assertEquals("eins", $this->testBean->getProperty("quuux/test/alpha/0"));
        $this->assertEquals("foobar", $this->testBean->getProperty("quuux/test/beta/foo"));
    }

    public function testGetPropertyWithEmptyPathComponent() 
    {
        $this->assertNull($this->testBean->getProperty("quux/"));
    }

    public function testAccessingPropertyWithMultipleSlashesAtTheBeginning() 
    {
        $array = array(
            "alpha" => "omega"
        );

        $bean = new MazeLib_Bean_TestBean($array);
        $this->assertNULL($bean->getProperty('////alpha'));
    }

    public function testGetPropertyWithSlashAtStart() 
    {
        $this->assertEquals("one", $this->testBean->getProperty('/foo'));
    }

    public function testSetPropertyWithGenericObject() 
    {
        $array = array(
            'ichi' => new stdClass()
        );
        $bean = new MazeLib_Bean_TestBean($array);

        $bean->setProperty('/ichi/ni', 'sans');
        $this->assertEquals('sans', $bean->getProperty('ichi/ni'));
    }

    public function testSetPropertyWithZendConfigShouldSetObject() 
    {
        $array = array(
            "alpha" => array("eins", "zwei", "drei"),
            "beta" => new MazeLib_Bean_TestBean()
        );
        $config = new Zend_Config($array, true);
        
        $this->testBean->setProperty('Zend/Config', $config);
        $this->assertInstanceOf('Zend_Config', $this->testBean->getProperty('Zend/Config'));
    }
    
    public function testGetPropertyInZendConfigShouldReturnProperties()
    {
        $array = array(
            "alpha" => array("eins", "zwei", "drei"),
            "beta" => new MazeLib_Bean_TestBean()
        );
        $config = new Zend_Config($array, true);
        
        $this->testBean->setProperty('Zend/Config', $config);

        $this->assertEquals('eins', $this->testBean->getProperty('Zend/Config/alpha/0'));
        $this->assertEquals('drei', $this->testBean->getProperty('Zend/Config/alpha/2'));
    }
    
    /**
     * @expectedException ErrorException
     */
    public function testSetPropertyWithZendConfigInDeepShouldThrowException()
    {
        $array = array(
            "alpha" => array("eins", "zwei", "drei"),
            "beta" => new MazeLib_Bean_TestBean()
        );
        $config = new Zend_Config($array, true);
        
        $this->testBean->setProperty('Zend/Config', $config);
        $this->testBean->setProperty('Zend/Config/alpha/0', 'zehn');
    }
    
    /**
     * extended maze bean tests
     */
    
    public function testGetPropertyOnNonExistingEntryShouldReturnNull()
    {
        $this->assertNull($this->bean->getProperty('nonExistent'));
    }
   
    public function testGetPropertyUnmappedOnNonExistingEntryShouldReturnNull()
    {
        $this->assertNull($this->bean->getProperty('nonExistent'));
    }

    public function testGetPropertyShouldReturnTheCorrectValue()
    {
        $this->bean->setProperty('one', 'ichi');
        $this->assertEquals('ichi', $this->bean->getProperty('one'));
    }
    
    public function testGetPropertyOnMazeValueShouldReturnUnmappedProperty()
    {
        $data = array(
            'local' => 'same',
            'status' => 1000,
            'remote' => 'same'
        );

        $this->bean->setProperty('array/key', $data);
        $this->assertEquals($data, $this->bean->getProperty('array/key'));
    }
    
    public function testAsArrayOnEmptyBeanShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->bean->asArray());
        $this->assertEmpty($this->bean->asArray());
    }
    
    public function testAsArrayShouldReturnOneEntry()
    {
        $this->bean->setProperty('test', 'test');
        $this->assertCount(1, $this->bean->asArray());
    }
    
    public function testSetPropertyShouldSetProperty()
    {
        $this->bean->setProperty('test', 'test');
        $this->assertEquals('test', $this->bean->getProperty('test'));
    }
    
    public function testSetPropertyWithArrrayShouldSetProperty()
    {
        $data = array(
            'test' => array(
                1 => 'test',
                3 => 'cheese'
            )
        );
        
        $this->bean->setProperty('test', $data);
        $this->assertEquals(array('test' => $data), $this->bean->asArray(true));
    }
    
    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetPropertyWithNonMazeValueInMazeShouldThrowException()
    {
        $this->bean->setProperty('array/key', 'test');
    }
    
    public function testSetPropertyWithMazeValueInNonMazePathSetMazeValue()
    {
        $data = array(
            'local' => 'maze val',
            'status' => 2000,
            'remote' => 'maze val'
        );
        
        $this->bean->setProperty('sample', $data);
        $this->assertEquals($data, $this->bean->getProperty('sample'));
    }
    
    public function testSetPropertyWithDeepStructShouldSetMazeValue()
    {
        $data = array(
            'local' => 'property set',
            'status' => 2,
            'remote' => ''
        );
        
        $this->bean->setProperty('deep/even/deeper/array/struct', $data);
        $this->assertEquals($data, $this->bean->getProperty('deep/even/deeper/array/struct'));
    }
    
    public function testSetPropertyOfMazePathWithWrongStatusShouldSetWrongStatus()
    {
        $valueProperty = array(
            'local' => 'nothing',
            'status' => 2000,
            'remote' => 'nothing'
        );
        
        $this->bean->setProperty('array/key', $valueProperty);
        $this->assertEquals(2000, $this->bean->getProperty('array/key/status'));
    }
    
    public function testSetPropertyWithMazeValueShouldOverwriteMazeValueFromBefore()
    {
        $data = array(
            'local' => 'gar',
            'status' => 3000,
            'remote' => 'gar'
        );
        
        $this->bean->setProperty('some/key', $data);
        $this->bean->setProperty('some/key', 'sum');
        $this->assertEquals('sum', $this->bean->getProperty('some/key'));
    }
    
    public function testSetPropertyWithMazeValueShouldOverwriteValueFromBefore()
    {
        $data1 = array(
            'local' => 'uno',
            'status' => 1,
            'remote' => 'des'
        );
        $data2 = array(
            'local' => 'gar',
            'status' => 1000,
            'remote' => 'gar'
        );
        
        $this->bean->setProperty('array/key', $data1);
        $this->bean->setProperty('array/key', $data2);
        $this->assertEquals($data2, $this->bean->getProperty('array/key'));
    }
    
    public function testSetPropertyWithStringShouldOverwriteMazeValueFromBefore()
    {
        $data1 = array(
            'local' => 'uno',
            'status' => 1,
            'remote' => 'des'
        );
        $data2 = array(
            'local' => 'gar',
            'status' => 1000,
            'remote' => 'gar'
        );
        
        $this->bean->setProperty('array/key', $data1);
        $this->bean->setProperty('array/key', $data2);
        $this->assertEquals($data2, $this->bean->getProperty('array/key'));
    }
    
    public function testSetBeanWithoutMazePathShouldSetValue()
    {
        $data = array(
            'array' => array(
                'key' => array(
                    'local' => 'test',
                    'status' => 2000,
                    'remote' => 'test'
                )
            )
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['array']['key'], $this->bean->getProperty('array/key'));
    }
    
    public function testSetBeanWithMazePathShouldSetMazeValue()
    {
        $data = array(
            'array/key' => array(
                'local' => 'test',
                'status' => 1000,
                'remote' => 'test'
            ),
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['array/key'], $this->bean->getProperty('array/key'));
    }
    
    public function testSetBeanShouldSetWrongMazeValueCode()
    {
        $data = array(
            'array/key' => array(
                'local' => 'test',
                'status' => 2000,
                'remote' => 'test'
            ),
        );
        
        $this->bean->setBean($data);
        $this->assertEquals($data['array/key'], $this->bean->getProperty('array/key'));
    }

    /**
     * @expectedException MazeLib_View_Bean_Exception
     */
    public function testSetBeanWithStringInMazeValueShouldThrowException()
    {
        $data = array(
            'array/key' => "justAString",
        );
        
        $this->bean->setBean($data);
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetBeanWithStringShouldThrowException()
    {
        $this->bean->setBean('justAString');
    }
    
    public function testGetPropertyWithPathIntoMazeValueShouldReturnThatValue()
    {
        $data = array(
            'array/key' => array(
                'local' => 'test',
                'status' => -1,
                'remote' => 'taste'
            )
        );
        
        $this->bean->setBean($data);
        
        $this->assertEquals('test', $this->bean->getProperty('array/key/local'));
        $this->assertEquals(-1, $this->bean->getProperty('array/key/status'));
        $this->assertEquals('taste', $this->bean->getProperty('array/key/remote'));
    }
    
    public function testHasConflictShouldReturnFalseOnNonExistentProperty()
    {
        $this->assertFalse($this->bean->hasConflict('nonExistent'));
    }
    
    public function testHasConflictShouldReturnFalseOnNonMazeValue()
    {
        $this->bean->setProperty('some/key', 'rarara');
        $this->assertFalse($this->bean->hasConflict('some/key'));
    }
    
    public function testHasConflictIOnSynchedMazeValueShouldReturnFalse()
    {
        $this->bean->setLocalProperty('array/key', 'val');
        $this->bean->setRemoteProperty('array/key', 'val');
        
        $this->assertFalse($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWithLocalAndRemoteSetFalseShouldReturnFalse()
    {
        $this->bean->setLocalProperty('array/key', false);
        $this->bean->setRemoteProperty('array/key', false);
        $this->assertFalse($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictWithLocalAndRemoteSetNullShouldReturnFalse()
    {
        $this->bean->setLocalProperty('array/key', null);
        $this->bean->setRemoteProperty('array/key', null);
        $this->assertFalse($this->bean->hasConflict('array/key'));
    }
    
    public function testHasConflictOnNonMazePathWithMazeValueShouldReturnFalse()
    {
        $data = array(
            'some/key' => array(
                'local' => 'test',
                'status' => -1,
                'remote' => 'taste'
            )
        );
        
        $this->bean->setBean($data);
        
        $this->assertFalse($this->bean->hasConflict('some/key'));
    }
    
    public function testGetConflictsOnEmptyBeanShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->bean->getConflicts());
        $this->assertEmpty($this->bean->getConflicts());
    }
    
    public function testGetConflictsWithOneSetShouldReturnArrayWith2Entries()
    {
        $data = array(
            'array/key' => 'uno',
            'maze/val' => 'dos'
        );
        
        $this->bean->setLocalData($data);
        $this->assertCount(2, $this->bean->getConflicts());
    }
    
    public function testGetConflictsWithTwoSetsShouldReturnArrayWith2Entries()
    {
        $this->bean->setLocalProperty('array/key', 'uno');
        $this->bean->setLocalProperty('maze/val', 'dos');
        $this->assertCount(2, $this->bean->getConflicts());
    }
    
    public function testGetConflictsIgnoresMazeValuesStructWhichAreNotInAMazePath()
    {
        $data = array(
            'some/key' => array(
                'local' => 'test',
                'status' => -1,
                'remote' => 'taste'
            )
        );
        
        $this->bean->setBean($data);
        
        $this->assertEmpty($this->bean->getConflicts());
    }
    
    public function testGetConflictsAfterSynchedMazeValueShouldReturnEmptyArray()
    {
        $this->bean->setLocalProperty('array/key', 'ichi');
        $this->bean->setRemoteProperty('array/key', 'ichi');
        
        $this->assertEmpty($this->bean->getConflicts());
    }
    
    public function testUnsetPropertyShouldRemoveFirstTierValueInBean()
    {
        $this->bean->setProperty('key', 'value');
        $this->bean->unsetProperty('key');
        
        $this->assertNull($this->bean->getProperty('key'));
    }
    
    public function testUnsetDeepPropertyShouldRemoveThatProperty()
    {
        $data = array(
            'local' => 'value',
            'status' => 1000,
            'remote' => 'value'
        );
        
        $this->bean->setProperty('array/key', $data);
        $this->bean->unsetProperty('array/key');
        
        $this->assertNull($this->bean->getProperty('array/key'));
    }
    
    public function testUnsetDeppPropertyShouldOnlyRemoveCertainProperty()
    {
        $data = array(
            'sample' => array(
                'key1' => 'val1',
                'key2' => 'val2',
                'key3' => 'val3'
            )
        );
        
        $this->bean->setBean($data);
        $this->bean->unsetProperty('sample/key1');
        
        unset($data['sample']['key1']);
        $this->assertEquals($data, $this->bean->asArray());
    }
    
    public function testUnsetWithGenericObjectShouldRemoveProperty()
    {
        $genericObject = new stdClass();
        $genericObject->test = 'wurst';
        
        $this->bean->setProperty('genericObject', $genericObject);
        $this->bean->unsetProperty('genericObject/test');
        
        $this->assertObjectNotHasAttribute('test', $this->bean->getProperty('genericObject'));
    }
    
    public function testUnsetPropertyOnNonExistingPropertyShouldReturnTrue()
    {
        $this->assertTrue($this->bean->unsetProperty('thispropertydoesnotexist'));
    }
    
    public function testUnsetPropertyOnNonExistingDeepPropertyShouldReturnTrue()
    {
        $this->assertTrue($this->bean->unsetProperty('thispropertydoesnotexist/no/never'));
    }
    
    public function testUnsetPropertyWithEmptyPropertyPathShouldReturnTrue()
    {
        $this->assertTrue($this->bean->unsetProperty(''));
    }
    
    public function testAsArrayShouldOnlyReturnInsertedProperties()
    {
        $result = array(
            't1' => 'test1',
            'deep' => array(
                't2' => 'test2'
            )
        );
        
        $this->bean->setProperty('t1', 'test1');
        $this->bean->setProperty('deep/t2', 'test2');
        
        $this->assertEquals($this->bean->asArray(), $result);
    }
    
    public function testAsArrayShouldMapMazeValuesAndReturnOnlyLocalValueForPath()
    {
        $result = array(
            'array' => array(
                'key' => 'value'
            )
        );
        
        $this->bean->setLocalProperty('array/key', 'value');
        
        $this->assertEquals($result, $this->bean->asArray());
    }
    
    public function testAsDeepArrayShouldOnlyReturnInsertedProperties()
    {
        $result = array(
            't1' => 'test1',
            'deep' => array(
                't2' => 'test2'
            )
        );
        
        $this->bean->setProperty('t1', 'test1');
        $this->bean->setProperty('deep/t2', 'test2');
        
        $this->assertEquals($this->bean->asDeepArray(), $result);
    }
    
    public function testAsDeepArrayShouldMapMazeValuesAndReturnOnlyLocalValueForPath()
    {
        $result = array(
            'array' => array(
                'key' => 'value'
            )
        );
        
        $this->bean->setLocalProperty('array/key', 'value');
        
        $this->assertEquals($result, $this->bean->asDeepArray());
    }
    
    public function testGetConflictWith1000ShouldReturnOnePositivConflict()
    {
        $data = array(
            'array/key' => array(
                'local' => 'test',
                'status' => 1,
                'remote' => 'taste'
            )
        );
        
        $this->bean->setBean($data);
        
        $this->assertCount(1, $this->bean->getConflicts(1000));
    }
    
    public function testGetConflictWithMinus1000ShouldReturnOnePositivConflict()
    {
        $data = array(
            'array/key' => array(
                'local' => 'test',
                'status' => -1,
                'remote' => 'taste'
            )
        );
        
        $this->bean->setBean($data);
        
        $this->assertCount(1, $this->bean->getConflicts(-1000));
    }
    
}

