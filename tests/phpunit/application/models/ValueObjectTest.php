<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObjectTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObjectTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * initialized sample with id sample1
     * 
     * @var Core_Model_ValueObject
     */
    protected $_sample1;
    
    public function setUp() {
        parent::setUp();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');
        
        $this->_sample1 = new Core_Model_ValueObject('sample1');
    }
    
    public function testGetIdOnUninitializedValueObjectShoudlReturnNull()
    {
        $valueObject = new Core_Model_ValueObject();
        
        $this->assertNull($valueObject->getId());
    }
    
    public function testGetIdOnInitializedValueObjectShouldReturnEqual()
    {
        $this->assertEquals('sample1', $this->_sample1->getId());
    }
    
    public function testGetBeanShouldReturnMazeBean()
    {
        $this->assertInstanceOf('MazeLib_Bean', $this->_sample1->getBean());
    }
    
    public function testGetBeanShouldCallLoad()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('load'), array('sample1'));
        $valueObject->expects($this->once())
                    ->method('load');
        
        $valueObject->getBean();
    }
    
    public function testLoadShouldCall_Load()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_load'), array('sample1'));
        $valueObject->expects($this->once())
                    ->method('_load');
        
        $valueObject->load();
    }
    
    public function testLoadWithUninitializedValueObjectShouldNotCall_Load()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_load'));
        $valueObject->expects($this->never())
                    ->method('_load');
        
        $valueObject->load();
    }
    
    public function testLoadCallsMultipleTimesShouldCall_LoadOnlyOneTime()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_load'), array('sample1'));
        $valueObject->expects($this->once())
                    ->method('_load');
        
        $valueObject->getBean();
        $valueObject->getBean();
        $valueObject->getBean();
        $valueObject->getBean();
    }
    
    public function testLoadShouldReturnTrue()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_load'), array('sample1'));
        $valueObject->expects($this->once())
                    ->method('_load')
                    ->will($this->returnValue(array('key1' => 'val1')));
        
        $this->assertTrue($valueObject->load());
    }
    
    public function testLoadShouldReturnTrueWhenNothingWasLoaded()
    {
        $this->assertTrue($this->_sample1->load());
    }
    
    public function testSetLoadedTrueShouldAvoidFirstLoading()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_load'), array('sample1'));
        $valueObject->expects($this->never())
                    ->method('_load');
        
        $valueObject->setLoaded(true);
        $valueObject->load();
    }
    
    public function testSetLoadedFalseShouldCall_LoadTwoTimes()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_load'), array('sample1'));
        $valueObject->expects($this->exactly(2))
                    ->method('_load');
        
        $valueObject->load();
        $valueObject->setLoaded(false);
        $valueObject->load();
    }
    
    public function testGetDataWithUninitializedValueObjectShouldReturnEmptyArray()
    {
        $valueObject = new Core_Model_ValueObject();
        
        $this->assertInternalType('array', $valueObject->getData());
        $this->assertEmpty($valueObject->getData());
    }
    
    public function testGetDataShouldReturnArray()
    {
        $this->assertInternalType('array', $this->_sample1->getData());
    }
    
    public function testSaveWithDataShouldCall_Save()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_save'));
        $valueObject->expects($this->once())
                    ->method('_save');
        
        $data = array(
            'val1' => 'key1'
        );
        
        $valueObject->setData($data)->save();
    }
    
    public function testSaveWithoutDataShouldCall_Save()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_save'));
        $valueObject->expects($this->once())
                    ->method('_save');
        
        $valueObject->save();
    }
    
    public function testSaveShouldReturnTrueWhen_SaveReturnsSameIdAsTheObjectInstance()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_save'), array('sample1'));
        $valueObject->expects($this->any())
                    ->method('_save')
                    ->will($this->returnValue('sample1'));
        
        $this->assertTrue($valueObject->save());
    }
    
    public function testSaveShouldReturnTrueWhen_SaveReturnsNotTheSameIdAsTheObjectInstance()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_save'), array('sample1'));
        $valueObject->expects($this->any())
                    ->method('_save')
                    ->will($this->returnValue('anotherId'));
        
        $this->assertTrue($valueObject->save());
    }
    
    public function testSaveShouldReturnFalseWhen_SaveReturnsFalse()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_save'), array('sample1'));
        $valueObject->expects($this->any())
                    ->method('_save')
                    ->will($this->returnValue(false));
        
        $this->assertFalse($valueObject->save());
    }
    
    public function testSaveNewIdFrom_SaveShouldOverrideExistingId()
    {
        $valueObject = $this->getMock('Core_Model_ValueObject', array('_save'), array('sample1'));
        $valueObject->expects($this->any())
                    ->method('_save')
                    ->will($this->returnValue('anotherId'));
        
        $valueObject->save();
        $this->assertEquals('anotherId', $valueObject->getId());
    }
    
    public function testSaveBuildsModifiedTimestamp()
    {
        $this->_sample1->save();
        
        $this->assertNotNull($this->_sample1->getData('modified'));
        $this->assertNotNull($this->_sample1->getData('modifiedReadable'));
    }
    
    public function testGetConflictsWithValidInitializedValueBeanShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample1->getConflicts());
        $this->assertEmpty($this->_sample1->getConflicts());
    }
    
    public function testGetConflictsWithValidUninitializedValueBeanShouldReturnEmptyArray()
    {
        $valueObject = new Core_Model_ValueObject();
        
        $this->assertInternalType('array', $valueObject->getConflicts());
        $this->assertEmpty($valueObject->getConflicts());
    }
    
    public function testGetRemoteDataWithValidInitializedValueBeanShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample1->getRemoteData());
        $this->assertEmpty($this->_sample1->getRemoteData());
    }
    
    public function testGetRemoteDataWithValidUninitializedValueBeanShouldReturnEmptyArray()
    {
        $valueObject = new Core_Model_ValueObject();
        
        $this->assertInternalType('array', $valueObject->getRemoteData());
        $this->assertEmpty($valueObject->getRemoteData());
    }
    
    public function testSetPropertyShouldBeEqualSetedProperty()
    {
        $this->_sample1->setProperty('key1', 'val1');
        
        $this->assertEquals('val1', $this->_sample1->getData('key1'));
    }
    
    public function testUnsetPropertyShouldRemoveFormerSetedProperty()
    {
        $this->_sample1->setProperty('key1', 'val1');
        
        $this->assertNotNull($this->_sample1->getData('key1'));
        $this->_sample1->unsetProperty('key1');
        
        $this->assertNull($this->_sample1->getData('key1'));
    }
    
}
