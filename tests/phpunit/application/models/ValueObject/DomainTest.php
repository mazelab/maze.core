<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_DomainTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_DomainTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * sample with data in data backend
     * 
     * @var Core_Model_ValueObject_Domain
     */
    protected $_sample;
    
    /**
     * @var string
     */
    protected $_sampleModulePath1 = '/samples/modules/configs/sample1.ini';
    
    /**
     * @var string
     */
    protected $_sampleModulePath2 = '/samples/modules/configs/sample2.ini';
    
    public function setUp() {
        parent::setUp();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');

        $this->_sample = Core_Model_DiFactory::newDomain('domainSample1');
    }
    
    public function tearDown() {
        parent::tearDown();
        
        Core_Model_DiFactory::getModuleRegistry()->setInstance();
    }
    
    public function testAddModuleShouldReturnFalseOnUninitializedInstance()
    {
        $client = Core_Model_DiFactory::newClient();
        
        $this->assertFalse($client->addService('email'));
    }
    
    public function testAddModuleWithNonExistingModuleShouldReturn()
    {
        $this->assertFalse($this->_sample->addService('nonexistent'));
    }
    
    public function testAddModuleShouldReturnTrue()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath1);
        
        $this->assertTrue($this->_sample->addService('validServiceSample1'));
    }
    
    public function testAddModuleShouldCreateModuleFieldsInDataset()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath1);
        
        $this->_sample->addService('validServiceSample1');
        
        $this->assertNotNull($this->_sample->getData('services'));
        $this->assertNotNull($this->_sample->getData('services/validServiceSample1'));
    }
    
    public function testAddModuleShouldSetModuleNameAndLabelInModuleDefinition()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath2);
        
        $this->_sample->addService('validServiceSample2');
        
        $this->assertNotNull($this->_sample->getData('services/validServiceSample2/name'));
        $this->assertNotNull($this->_sample->getData('services/validServiceSample2/label'));
    }
    
    public function testAddModuleWithValidModuleShouldCallSave()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath2);
        
        $client = $this->getMock('Core_Model_ValueObject_Client', array('save'), array('sample1'));
        $client->expects($this->once())
             ->method('save');
        
        $client->addService('validServiceSample2');
    }
    
    public function testHasModuleShouldReturnTrue()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath1);
        
        $this->_sample->addService('validServiceSample1');
        
        $this->assertTrue($this->_sample->hasService('validServiceSample1'));
    }
    
    public function testHasModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_sample->hasService('validServiceSample1'));
    }
    
    public function testGetModulesShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getServices());
        $this->assertEmpty($this->_sample->getServices());
    }
    
    public function testGetModulesShouldReturnNotEmptyArray()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath1);
        
        $this->_sample->addService('validServiceSample1');
        
        $this->assertInternalType('array', $this->_sample->getServices());
        $this->assertNotEmpty($this->_sample->getServices());
    }
    
    public function testGetModulesShouldReturnArrayWith2Properties()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath1);
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleModulePath2);
        
        $this->_sample->addService('validServiceSample1');
        $this->_sample->addService('validServiceSample2');
        
        $this->assertCount(2, $this->_sample->getServices());
    }
    
    public function testAddAdditionalFieldShouldReturnFalseWithUnitializedDomain()
    {
        $domain = Core_Model_DiFactory::newDomain();
        
        $this->assertFalse($domain->addAdditionalField('key1', 'val1'));
    }
    
    public function testAddAdditionalFieldShouldReturnFalseWithNonStringKeyOrValue()
    {
        $this->assertFalse($this->_sample->addAdditionalField('key1', array()));
        $this->assertFalse($this->_sample->addAdditionalField(new stdClass(), 'val1'));
    }
    
    public function testAddAdditionalFieldShouldReturnMd5edKey()
    {
        $this->assertEquals(md5('foo'), $this->_sample->addAdditionalField('foo', 'bar'));
    }
    
    public function testAddAdditionalFieldShouldCreateAdditionalFieldArrayStruct()
    {
        $this->_sample->addAdditionalField('foo', 'bar');
        
        $this->assertInternalType('array', $this->_sample->getData('additionalFields'));
        $this->assertNotNull($this->_sample->getData('additionalFields'));
    }
    
    public function testAddAddionalFieldShouldCreateAdditionalFieldsKeyAsMd5()
    {
        $this->_sample->addAdditionalField('foo', 'bar');
        
        $this->assertNotNull($this->_sample->getData('additionalFields/' . md5('foo')));
    }
    
    public function testAddAdditionalFieldShouldCreateLabelAndValueFields()
    {
        $struct = array(
            'label' => 'foo',
            'value' => 'bar'
        );
        
        $this->_sample->addAdditionalField('foo', 'bar');
        
        $this->assertEquals($struct, $this->_sample->getData('additionalFields/' . md5('foo')));
    }
    
    public function testAddAdditionalFieldWithValidDataShouldCallSave()
    {
        $domain = $this->getMock('Core_Model_ValueObject_Domain', array('save'), array('sample1'));
        $domain->expects($this->once())
             ->method('save');
        
        $domain->addAdditionalField('uno', 'one');
    }
    
    public function testDeleteAdditionalFieldShouldReturnTrue()
    {
        $this->_sample->addAdditionalField('foo', 'var');
        
        $this->assertTrue($this->_sample->deleteAdditionalField('foo'));
    }
    
    public function testDeleteAdditionalFieldWithNonExistentFieldShouldReturnTrue()
    {
        $this->assertTrue($this->_sample->deleteAdditionalField('foo'));
    }
    
    public function testDeleteAdditionalFieldShouldRemovePropertyFromData()
    {
        $this->_sample->addAdditionalField('foo', 'var');
        
        $this->assertNotNull($this->_sample->getData('additionalFields/' . md5('foo')));
        
        $this->_sample->deleteAdditionalField(md5('foo'));
                
        $this->assertNull($this->_sample->getData('additionalFields/' . md5('foo')));        
    }
    
    public function testSaveShouldCallSetSearchIndexWhenNameWasSeted()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->once())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('name' => 'test'))->save();
    }
    
    public function testSaveShouldCallSetSearchIndexWhenIpAddressWasSeted()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->once())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('name' => 'foo.bar'))->save();
    }
    
    public function testSaveShouldntCallSetSearchIndexWhenChangingRndField()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->never())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('rnd' => 'bzu'))->save();
    }
    
    public function testGetNameShouldBeEqual()
    {
        $domain = Core_Model_DiFactory::newDomain();
        
        $domain->setData(array('name' => 'test'));
        
        $this->assertEquals('test', $domain->getName());
    }
    
    public function testGetOwnerWithUninitializedDomainShouldReturnNull()
    {
        $domain = Core_Model_DiFactory::newDomain();
        
        $this->assertNull($domain->getOwner());
    }
    
    public function testGetOwnerShouldReturnClientValueObject()
    {
        $this->assertInstanceOf('Core_Model_ValueObject_Client', $this->_sample->getOwner());
    }
    
    public function testGetOwnerWithNonExistingOwnerShouldReturnNull()
    {
        $this->_sample->setData(array('owner' => 'notExistent'));
        
        $this->assertNull($this->_sample->getOwner());
    }
    
}
