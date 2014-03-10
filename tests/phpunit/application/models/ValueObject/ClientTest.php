<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_ClientTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_ClientTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * sample with data in data backend
     * 
     * @var Core_Model_ValueObject_Client
     */
    protected $_sample;
    
    /**
     * @var string
     */
    protected $_sampleServicePath1 = '/samples/modules/configs/sample1.ini';
    
    /**
     * @var string
     */
    protected $_sampleServicePath2 = '/samples/modules/configs/sample2.ini';
    
    public function setUp() {
        parent::setUp();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');

        $this->_sample = Core_Model_DiFactory::newClient('clientSample1');
    }
    
    public function tearDown() {
        parent::tearDown();
        
        Core_Model_DiFactory::getModuleRegistry()->setInstance();
    }
    
    public function testAddServiceShouldReturnFalseOnUninitializedInstance()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath1);
        
        $client = Core_Model_DiFactory::newClient();
        
        $this->assertFalse($client->addService('validServiceSample1'));
    }
    
    public function testAddServiceWithNonExistingServiceShouldReturn()
    {
        $this->assertFalse($this->_sample->addService('nonexistent'));
    }
    
    public function testAddServiceShouldReturnTrue()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath1);
        
        $this->assertTrue($this->_sample->addService('validServiceSample1'));
    }
    
    public function testAddServiceShouldCreateServiceFieldsInDataset()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath1);
        
        $this->_sample->addService('validServiceSample1');
        
        $this->assertNotNull($this->_sample->getData('services'));
        $this->assertNotNull($this->_sample->getData('services/validServiceSample1'));
    }
    
    public function testAddServiceShouldSetServiceNameAndLabelInServiceDefinition()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath2);
        
        $this->_sample->addService('validServiceSample2');
        
        $this->assertNotNull($this->_sample->getData('services/validServiceSample2/name'));
        $this->assertNotNull($this->_sample->getData('services/validServiceSample2/label'));
    }
    
    public function testAddServiceWithValidServiceShouldCallSave()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath2);
        
        $client = $this->getMock('Core_Model_ValueObject_Client', array('save'), array('sample1'));
        $client->expects($this->once())
             ->method('save');
        
        $client->addService('validServiceSample2');
    }
    
    public function testHasServiceShouldReturnTrue()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath1);
        
        $this->_sample->addService('validServiceSample1');
        
        $this->assertTrue($this->_sample->hasService('validServiceSample1'));
    }
    
    public function testHasServiceShouldReturnFalse()
    {
        $this->assertFalse($this->_sample->hasService('sampleService2'));
    }
    
    public function testGetServicesShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getServices());
        $this->assertEmpty($this->_sample->getServices());
    }
    
    public function testGetServicesShouldReturnNotEmptyArray()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath1);
        
        $this->_sample->addService('validServiceSample1');
        
        $this->assertInternalType('array', $this->_sample->getServices());
        $this->assertNotEmpty($this->_sample->getServices());
    }
    
    public function testGetServicesShouldReturnArrayWith2Properties()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath1);
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_sampleServicePath2);
        
        $this->_sample->addService('validServiceSample1');
        $this->_sample->addService('validServiceSample2');
        
        $this->assertCount(2, $this->_sample->getServices());
    }
    
    public function testAddAdditionalFieldShouldReturnFalseWithUnitializedClient()
    {
        $client = Core_Model_DiFactory::newClient();
        
        $this->assertFalse($client->addAdditionalField('key1', 'val1'));
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
        $client = $this->getMock('Core_Model_ValueObject_Client', array('save'), array('sample1'));
        $client->expects($this->once())
             ->method('save');
        
        $client->addAdditionalField('uno', 'one');
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
    
    public function testSaveShouldCallSetSearchIndexWhenCompanyWasSeted()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->once())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('company' => 'test'))->save();
    }
    
    public function testSaveShouldCallSetSearchIndexWhenPreNameWasSeted()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->once())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('prename' => 'foo'))->save();
    }
    
    public function testSaveShouldCallSetSearchIndexWhenSurNameWasSeted()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->once())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('surname' => 'bar'))->save();
    }
    
    public function testSaveShouldCallSetSearchIndexWhenStatusWasSeted()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->once())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('status' => false))->save();
    }
    
    public function testSaveShouldCallSetSearchIndexWhenAvatarWasSeted()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->once())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('avatar' => 'jk880as983teg8zadsgi'), true)->save();
    }
    
    public function testSaveShouldntCallSetSearchIndexWhenChangingRndField()
    {
        $searchIndex = $this->getMock('Core_Model_Search_Index', array('setSearchIndex'));
        $searchIndex->expects($this->never())
                   ->method('setSearchIndex');
        
        Core_Model_DiFactory::setSearchIndex($searchIndex);
        
        $this->_sample->setData(array('rnd' => 'bzu'))->save();
    }
    
    public function testActivateShouldSetStatusPropertyToTrue()
    {
        $this->_sample->activate();
        
        $this->assertTrue($this->_sample->getData('status'));
    }
    
    public function testActivateShouldCallSave()
    {
        $client = $this->getMock('Core_Model_ValueObject_Client', array('save'), array('clientSample1'));
        $client->expects($this->once())
               ->method('save');
        
        $client->activate();
    }
    
    public function testDeactivateShouldSetStatusPropertyToTrue()
    {
        $this->_sample->unsetProperty('status');
        $this->_sample->deactivate();
        
        $this->assertFalse($this->_sample->getData('status'));
    }
    
    public function testDeactivateShouldCallSave()
    {
        $client = $this->getMock('Core_Model_ValueObject_Client', array('save'), array('clientSample1'));
        $client->expects($this->once())
               ->method('save');
        
        $client->deactivate();
    }
    
    public function testGetDomainsWithUninitializedClientShouldReturnEmptyArray()
    {
        $client = Core_Model_DiFactory::newClient();
        
        $this->assertInternalType('array', $client->getDomains());
        $this->assertEmpty($client->getDomains());
    }
    
    public function testGetDomainsShouldReturnNotEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getDomains());
        $this->assertNotEmpty($this->_sample->getDomains());
    }
    
    public function testGetDomainsShouldReturn2Entries()
    {
        $this->assertCount(2, $this->_sample->getDomains());
    }
    
    public function testGetDomainsShouldBeInstanceOfValueObjectDomain()
    {
        foreach($this->_sample->getDomains() as $domain) {
            $this->assertInstanceOf('Core_Model_ValueObject_Domain', $domain);
        }
    }
    
    public function testGetLabelWithUninitializedClientShouldReturnNull()
    {
        $client = Core_Model_DiFactory::newClient();
        
        $this->assertNull($client->getLabel());
    }

    public function testGetLabelWithInitializedClientShouldBeEqual()
    {
        $this->assertEquals('client sample 1', $this->_sample->getLabel());
    }
    
    public function testLabelPropertyShouldBeCreatedWhenSetCompany()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('company' => 'Test'));
        
        $this->assertNotNull($client->getLabel());
    }
    
    public function testLabelPropertyShouldBeCreatedWhenSetPreName()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('prename' => 'foo'));
        
        $this->assertNotNull($client->getLabel());
    }
    
    public function testLabelPropertyShouldBeCreatedWhenSetSurName()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('surname' => 'bar'));
        
        $this->assertNotNull($client->getLabel());
    }
    
    public function testLabelProperyShouldBeEqualCompanyAfterCompanySet()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('surname' => 'bar', 'prename' => 'foo', 'company' => 'testa'));
        
        $this->assertEquals('testa', $client->getLabel());
    }
    
    public function testLabelPropertyShouldBeEqualCombinationOfPrenameAndSurname()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('surname' => 'bar', 'prename' => 'foo'));
        
        $this->assertEquals('bar foo', $client->getLabel());
    }
    
    public function testLabelProperyShouldBeEqualCombinationOfPrenameAndSurnameAfterSetCompanyPropertyToNull()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('surname' => 'bar', 'prename' => 'foo', 'company' => 'testa'));
        
        $this->assertEquals('testa', $client->getLabel());
        
        $client->setData(array('company' => null));
        
        $this->assertEquals('bar foo', $client->getLabel());
    }
    
    public function testLabelProperyShouldBeEqualCombinationOfPrenameAndSurnameAfterUnsetCompanyProperty()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('surname' => 'bar', 'prename' => 'foo', 'company' => 'testa'));
        
        $this->assertEquals('testa', $client->getLabel());
        
        $client->unsetProperty('company');
        
        $this->assertEquals('bar foo', $client->getLabel());
    }
    
    public function testLabelProperyShouldBeEqualCombinationOfPrenameAndSurnameAfterUnsetCompanyWithSlashesProperty()
    {
        $client = Core_Model_DiFactory::newClient();

        $client->setData(array('surname' => 'bar', 'prename' => 'foo', 'company' => 'testa'));
        
        $this->assertEquals('testa', $client->getLabel());
        
        $client->unsetProperty('/company');
        
        $this->assertEquals('bar foo', $client->getLabel());
    }
    
    public function testSetDataShouldSetPasswordAsMd5()
    {
        $this->_sample->setData(array('password' => 'phpuniting'));
        
        $this->assertEquals(md5('phpuniting'), $this->_sample->getData('password'));
    }
    
    public function testSetDataShouldSkipPasswordEncryption()
    {
        $this->_sample->setData(array('password' => 'phpuniting'), false, true);
        
        $this->assertEquals('phpuniting', $this->_sample->getData('password'));
    }

    public function testSetDataAvatarShouldCallUploadAvatar()
    {
        $client = $this->getMock('Core_Model_ValueObject_Client', array('uploadAvatar'), array('clientSample1'));
        $client->expects($this->once())
               ->method('uploadAvatar')
               ->will($this->returnValue(false));
        
        $client->setData(array('avatar' => 'pic.gif'));
    }
    
    public function testSetDataAvatarShouldNotCallUploadAvatar()
    {
        $client = $this->getMock('Core_Model_ValueObject_Client', array('uploadAvatar'), array('clientSample1'));
        $client->expects($this->never())
               ->method('uploadAvatar');
        
        $client->setData(array('avatar' => 'pic.gif'), true);
    }
    
    public function testGetEmailShouldBeEqual()
    {
        $client = Core_Model_DiFactory::newClient();
        
        $client->setData(array('email' => 'test'));
        
        $this->assertEquals('test', $client->getEmail());
    }
    
    public function testGetStatusShouldBeEqual()
    {
        $client = Core_Model_DiFactory::newClient();
        
        $client->setData(array('status' => 'test'));
        
        $this->assertEquals('test', $client->getStatus());
    }
    
    public function testGetUsernameShouldBeEqual()
    {
        $client = Core_Model_DiFactory::newClient();
        
        $client->setData(array('username' => 'test'));
        
        $this->assertEquals('test', $client->getUsername());
    }
    
}
