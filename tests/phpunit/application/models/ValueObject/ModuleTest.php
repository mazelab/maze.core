<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_ModuleTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 * @see ZendX_View_Autoescape
 */
class Core_Model_ValueObject_ModuleTest extends PHPUnit_Framework_TestCase
{

    /**
     * sample unconfigured and without data in data backend
     * 
     * @var Core_Model_ValueObject_Module
     */
    protected $_sample;
    
    /**
     * @var array
     */
    protected $_sampleConfig = array(
        'name' => 'sampleModule8',
        'label' => 'sample Module 8',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'repository' => array(
            'name' => 'sample/8',
            'version' => '1.0.0',
            'url' => 'http://vendor.sample/sample8',
            'type' => 'vcs'
        )
    );
    
    /**
     * @var array
     */
    protected $_sampleValidUpdate = array(
        'name' => 'sampleModule8',
        'label' => 'sample Module 8',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'installed' => true,
        'repository' => array(
            'name' => 'sample/8',
            'version' => '1.0.5',
            'url' => 'http://vendor.sample/sample8',
            'type' => 'vcs'
        )
    );
    
    /**
     * @var array
     */
    protected $_sampleValidUpdate2 = array(
        'name' => 'sampleModule7',
        'label' => 'sample Module 7',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'installed' => true,
        'repository' => array(
            'name' => 'sample/7',
            'version' => '1.0.5',
            'url' => 'http://vendor.sample/sample7',
            'type' => 'vcs'
        )
    );
    
    /**
     * @var array
     */
    protected $_sampleValidUpdate3 = array(
        'name' => 'sampleModule6',
        'label' => 'sample Module 6',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'installed' => true,
        'repository' => array(
            'name' => 'sample/6',
            'version' => '1.130.54',
            'url' => 'http://vendor.sample/sample6',
            'type' => 'vcs'
        )
    );
    
    /**
     * @var array
     */
    protected $_sampleValidUpdate4 = array(
        'name' => 'sampleModule8',
        'label' => 'sample Module 8',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'installed' => true,
        'repository' => array(
            'name' => 'sample/8',
            'version' => '110.0.45',
            'url' => 'http://vendor.sample/sample8',
            'type' => 'vcs'
        )
    );
    
    /**
     * @var array
     */
    protected $_sampleValidUpdate5 = array(
        'name' => 'sampleModule8',
        'label' => 'sample Module 8',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'installed' => true,
        'repository' => array(
            'name' => 'sample/8',
            'version' => '1.12',
            'url' => 'http://vendor.sample/sample8',
            'type' => 'vcs'
        )
    );
    
    /**
     * @var array
     */
    protected $_sampleInvalidUpdate = array(
        'name' => 'sampleModule8',
        'label' => 'sample Module 8',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'installed' => true,
        'repository' => array(
            'name' => 'sample/8',
            'version' => 'False Version',
            'url' => 'http://vendor.sample/sample8',
            'type' => 'vcs'
        )
    );
    
    /**
     * @var array
     */
    protected $_sampleInvalidUpdate2 = array(
        'name' => 'sampleModule8',
        'label' => 'sample Module 8',
        'vendor' => 'sample',
        'description' => 'sample for installed module',
        'installed' => true,
        'repository' => array(
            'name' => 'sample/8',
            'version' => '12.31.dev',
            'url' => 'http://vendor.sample/sample8',
            'type' => 'vcs'
        )
    );
    
    public function setUp() {
        parent::setUp();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');
        
        $this->_sample = Core_Model_DiFactory::newModule('sampleModule8');
        $this->_sample->setModuleConfig($this->_sampleConfig);
    }
    
    public function testGetNameWithEmptyModuleShoudlReturnNull()
    {
        $modules = Core_Model_DiFactory::newModule();
        
        $this->assertNull($modules->getName());
    }
    
    public function testGetNameShouldReturnString()
    {
        $this->assertInternalType('string', $this->_sample->getName());
    }
    
    public function testGetNameModuleShouldBeEqual()
    {
        $this->assertEquals('sampleModule8', $this->_sample->getName());
    }
    
    public function testGetNameShouldBeSameAsGetId()
    {
        $this->assertEquals($this->_sample->getId(), $this->_sample->getName());
    }
    
    public function testGetLabelWitEmptyModuleShouldReturnNull()
    {
        $module = Core_Model_DiFactory::newModule('sample');
        
        $this->assertNull($module->getLabel());
    }
    
    public function testGetLabelShouldReturnString()
    {
        $this->assertInternalType('string', $this->_sample->getLabel());
    }
    
    public function testGetConfigWithEmptyModuleShouldReturnNull()
    {
        $module = Core_Model_DiFactory::newModule('sample');
        
        $this->assertNull($module->getModuleConfig());
    }
    
    public function testGetConfigShouldReturnNotEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getModuleConfig());
        $this->assertNotEmpty($this->_sample->getModuleConfig());
    }
    
    public function testGetConfigWithParamAndEmptyModuleShouldReturnNull()
    {
        $this->assertNull($this->_sample->getModuleConfig('routes/config/client/route'));
    }
    
    public function testSetConfigWithArrayShouldReturnModuleValueObjectInstance()
    {
        $this->assertInstanceOf('Core_Model_ValueObject_Module', $this->_sample->setModuleConfig(array()));
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetConfigWithOutArrayShouldThrowException()
    {
        $this->_sample->setModuleConfig('config');
    }
    
    public function testGetClientConfigWithEmptyModuleShouldReturnEmptyArray()
    {
        $module = Core_Model_DiFactory::newModule('sample');
        
        $this->assertInternalType('array', $module->getClientConfig());
        $this->assertEmpty($module->getClientConfig());
    }
    
    public function testGetClientConfigWithoutExistingClientWithModuleWithoutDataShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getClientConfig('notExistent'));
        $this->assertEmpty($this->_sample->getClientConfig('notExistent'));
    }
    
    public function testGetClientConfigWithNotExistentClientShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getClientConfig('notExistent'));
        $this->assertEmpty($this->_sample->getClientConfig('notExistent'));
    }
    
    public function testGetClientConfigWithExistentClientShouldReturnNotEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getClientConfig('clientSample1'));
        $this->assertNotEmpty($this->_sample->getClientConfig('clientSample1'));
    }
    
    public function testAddClientConfigShouldSetClientConfigInData()
    {
        $data = array(
            'foo' => 'bar'
        );
        
        $this->_sample->addClientConfig('clientSample2', $data);
        $this->assertEquals($data, $this->_sample->getClientConfig('clientSample2'));
    }
    
    public function testAddClientConfigShouldNotAutomaticalySaveData()
    {
        $data = array(
            'foo' => 'bar'
        );
        
        $this->_sample->addClientConfig('clientSample2', $data);
        
        /**
         * will reload data, so seted but not saved data will vanish
         */
        $this->_sample->setLoaded(false);
        $this->assertNotEquals($data, $this->_sample->getClientConfig('clientSample2'));
    }
    
    public function testGetDomainConfigWithEmptyModuleShouldReturnEmptyArray()
    {
        $module = Core_Model_DiFactory::newModule('sample');
        
        $this->assertInternalType('array', $module->getDomainConfig());
        $this->assertEmpty($module->getDomainConfig());
    }
    
    public function testGetDomainConfigWithoutExistingDomainWithModuleWithoutDataShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getDomainConfig('notExistent'));
        $this->assertEmpty($this->_sample->getDomainConfig('notExistent'));
    }
    
    public function testGetDomainConfigWithNotExistentDomainShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getDomainConfig('notExistent'));
        $this->assertEmpty($this->_sample->getDomainConfig('notExistent'));
    }
    
    public function testGetDomainConfigWithExistentDomainShouldReturnNotEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getDomainConfig('domainSample1'));
        $this->assertNotEmpty($this->_sample->getDomainConfig('domainSample1'));
    }
    
    public function testAddDomainConfigShouldSetDomainConfigInData()
    {
        $data = array(
            'foo' => 'bar'
        );
        
        $this->_sample->addDomainConfig('domainSample2', $data);
        $this->assertEquals($data, $this->_sample->getDomainConfig('domainSample2'));
    }
    
    public function testAddDomainConfigShouldNotAutomaticalySaveData()
    {
        $data = array(
            'foo' => 'bar'
        );
        
        $this->_sample->addDomainConfig('domainSample2', $data);
        
        /**
         * will reload data, so seted but not saved data will vanish
         */
        $this->_sample->setLoaded(false);
        $this->assertNotEquals($data, $this->_sample->getDomainConfig('domainSample2'));
    }
    
    public function testGetNodeConfigWithEmptyModuleShouldReturnEmptyArray()
    {
        $module = Core_Model_DiFactory::newModule('sample');
        
        $this->assertInternalType('array', $module->getNodeConfig());
        $this->assertEmpty($module->getNodeConfig());
    }
    
    public function testGetNodeConfigWithoutExistingNodeWithModuleWithoutDataShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getNodeConfig('notExistent'));
        $this->assertEmpty($this->_sample->getNodeConfig('notExistent'));
    }
    
    public function testGetNodeConfigWithNotExistentNodeShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getNodeConfig('notExistent'));
        $this->assertEmpty($this->_sample->getNodeConfig('notExistent'));
    }
    
    public function testGetNodeConfigWithExistentNodeShouldReturnNotEmptyArray()
    {
        $this->assertInternalType('array', $this->_sample->getNodeConfig('nodeSample1'));
        $this->assertNotEmpty($this->_sample->getNodeConfig('nodeSample1'));
    }
    
    public function testAddNodeConfigShouldSetNodeConfigInData()
    {
        $data = array(
            'foo' => 'bar'
        );
        
        $this->_sample->addNodeConfig('nodeSample2', $data);
        $this->assertEquals($data, $this->_sample->getNodeConfig('nodeSample2'));
    }
    
    public function testAddNodeConfigShouldNotAutomaticalySaveData()
    {
        $data = array(
            'foo' => 'bar'
        );
        
        $this->_sample->addNodeConfig('nodeSample2', $data);
        
        /**
         * will reload data, so seted but not saved data will vanish
         */
        $this->_sample->setLoaded(false);
        $this->assertNotEquals($data, $this->_sample->getNodeConfig('nodeSample2'));
    }

    public function testSyncModuleUpdateShouldReturnTrue()
    {
        $this->assertTrue($this->_sample->syncModuleUpdate($this->_sampleValidUpdate));
    }
    
    public function testSyncModuleUpdateWithInvalidVersionShouldReturnFalse()
    {
        $this->assertFalse($this->_sample->syncModuleUpdate($this->_sampleInvalidUpdate));
    }
    
    public function testSyncModuleUpdateWithSameVersionShouldReturnFalse()
    {
        $mock = $this->getMock('Core_Model_ValueObject_Module', array('isInstalled'), array('sampleModule8'));
        $mock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        
        $mock->setModuleConfig($this->_sampleConfig);
        
        $this->assertFalse($mock->syncModuleUpdate($this->_sample->getData()));
    }
    
    public function testSyncModuleUpdateWithLowerVersionShouldReturnFalse()
    {
        $mock = $this->getMock('Core_Model_ValueObject_Module', array('isInstalled'), array('sampleModule8'));
        $mock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        
        $mock->setModuleConfig($this->_sampleConfig);
        
        $data = $mock->getData();
        $data['repository']['version'] = '0.0.1';
        
        $this->assertFalse($mock->syncModuleUpdate($data));
    }
    
    public function testSyncModuleUpdateWithoutRepositoryDataShouldReturnFalse()
    {
        $data = $this->_sampleInvalidUpdate;
        unset($data['repository']);
        
        $this->assertFalse($this->_sample->syncModuleUpdate($data));
    }
    
    public function testSyncModuleUpdateShouldSetUpdateableFlag()
    {
        $mock = $this->getMock('Core_Model_ValueObject_Module', array('isInstalled'), array('sampleModule8'));
        $mock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        
        $mock->setModuleConfig($this->_sampleConfig);
        
        $mock->syncModuleUpdate($this->_sampleValidUpdate);
        
        $this->assertTrue($mock->getData('updateable'));
    }
    
    public function testSyncModuleUpdateShouldSetUpdateDataInUpdateKey()
    {
        $mock = $this->getMock('Core_Model_ValueObject_Module', array('isInstalled'), array('sampleModule8'));
        $mock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        
        $mock->setModuleConfig($this->_sampleConfig);
        
        $mock->syncModuleUpdate($this->_sampleValidUpdate);
        
        $this->assertEquals($this->_sampleValidUpdate, $mock->getData('update'));
    }
    
    public function testSyncModuleUpdateOfModuleWithoutRepositoryInformationShouldReturnTrue()
    {
        $module = new Core_Model_ValueObject_Module('sampleModule7');
        
        $this->assertTrue($module->syncModuleUpdate($this->_sampleValidUpdate2));
    }
    
    public function testSyncModuleUpdateOfModuleWithoutRepositoryInformationShouldSetRemoteRepositoryData()
    {
        $module = new Core_Model_ValueObject_Module('sampleModule7');
        
        $module->syncModuleUpdate($this->_sampleValidUpdate2);
        
        $this->assertEquals($this->_sampleValidUpdate2['repository'], $module->getData('repository'));
    }
    
    public function testSyncModuleUpdateOfModuleWithoutRepositoryInformationShouldOnlySetRemoteRepositoryData()
    {
        $module = new Core_Model_ValueObject_Module('sampleModule7');
        
        $module->syncModuleUpdate($this->_sampleValidUpdate);
        
        $this->assertNotEquals($this->_sampleValidUpdate['name'], $module->getName());
        $this->assertNotEquals($this->_sampleValidUpdate['label'], $module->getLabel());
        $this->assertNotEquals($this->_sampleValidUpdate['description'], $module->getData('description'));
    }
    
    public function testSyncModuleUpdateWithOtherModuleDataShouldReturnFalse()
    {
        $module = new Core_Model_ValueObject_Module('sampleModule6');
        
        $this->assertFalse($module->syncModuleUpdate($this->_sampleValidUpdate));
    }
    
    public function testSyncModuleUpdateWithNotInstalledModelShouldUpdateDataSet()
    { 
        $module = new Core_Model_ValueObject_Module('sampleModule6');
        
        $module->syncModuleUpdate($this->_sampleValidUpdate3);
        $data = $module->getData();
        unset($data['_id']);
        
        $this->assertEquals($this->_sampleValidUpdate3, $data);
    }
    
    public function testIsInstalledShouldReturnTrue()
    {
        $module = new Core_Model_ValueObject_Module('example');
        
        $module->setData(array('installed' => true));
        
        $this->assertTrue($module->isInstalled());
    }
    
    public function testIsInstalledShouldReturnFalse()
    {
        $module = new Core_Model_ValueObject_Module('example');
        
        $this->assertFalse($module->isInstalled());
    }
    
    public function testIsInstalledWithInitializedConfigShouldSetInstallFlag()
    {
        $module = new Core_Model_ValueObject_Module('example');
        
        $module->setModuleConfig($this->_sampleConfig);
        
        $this->assertTrue($module->isInstalled());
    }
    
    public function testSyncModuleUpdateWithVersionHigherThenTenShouldReturnTrue()
    {
        $module = new Core_Model_ValueObject_Module('sampleModule8');
        
        $this->assertTrue($module->syncModuleUpdate($this->_sampleValidUpdate4));
    }
    
    public function testSyncModuleUpdateWithOnlyOneDotInVersionShouldReturnTrue()
    {
        $module = new Core_Model_ValueObject_Module('sampleModule8');
        
        $this->assertTrue($module->syncModuleUpdate($this->_sampleValidUpdate5));
    }

    public function testSyncModuleUpdateWithLettesInVersionShouldReturnFalse()
    {
        $this->assertFalse($this->_sample->syncModuleUpdate($this->_sampleInvalidUpdate2));
    }
    
}
