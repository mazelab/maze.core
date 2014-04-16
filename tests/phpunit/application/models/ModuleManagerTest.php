<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ModuleManagerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ModuleManagerTest extends PHPUnit_Framework_TestCase
{

    /**
     * valid sample module data in order to test add module
     * 
     * @var array
     */
    protected $_sample1 = array(
        'name' => 'sampleAddModule1',
        'label' => 'sample add module 1',
        'description' => 'sample to Test module add',
        'vendor' => 'sample',
        'repository' => array(
            'url' => 'https://sample.vendor/add1',
            'type' => 'vcs',
            'version' => '0.0.1',
            'name' => 'add1'
        )
    );
    
    /**
     * valid sample module data with source as path in order to test add module
     * 
     * @var array
     */
    protected $_sample2 = array(
        'name' => 'sampleAddModule2',
        'label' => 'sample add module 2',
        'description' => 'sample add module 2',
        'vendor' => 'sample',
        'repository' => array(
            'url' => '/sample/addModule2',
            'type' => 'vcs',
            'version' => '0.0.1',
            'name' => 'sample/addModule2'
        )
    );
    
    /**
     * invvalid sample module data in order to test add module
     * 
     * @var array
     */
    protected $_sampleInvalid = array(
        'name' => 'sampleInvalid',
        'label' => 'sample invalid 1',
        'description' => 'sample to invalid module add',
        'vendor' => 'sample',
        'repository' => array(
            'url' => 'https://sample.vendor/invalid',
        )
    );
    
    /**
     * valid sample module data in order to test add module
     * 
     * @var array
     */
    protected $_sampleInvalid2 = array(
        'name' => 'sampleInvalid2',
        'label' => 'sample add invalid module 2',
        'description' => 'sample add invalid module 2',
        'vendor' => 'sample',
        'repository' => array(
            'url' => '/sample/invalid2',
            'type' => 'vcs',
            'version' => 'invalid Version',
            'name' => 'sample/invalid2'
        )
    );
    
    /**
     * sample of additional field
     *
     * @var array
     */
    protected $_additionalField = array(
        "additionalKey" => "foo",
        "additionalValue" => "bar"
    );
    
    /**
     * @var string
     */
    protected $_composerPath;

    /**
     * mocked Core_Model_Module_Composer instance
     * 
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_composerMock;
    
    /**
     * @var string
     */
    protected $_composerTemplate = array(
        'name' => 'cdsinternetagentur/maze.dashboard',
        'description' => 'test module manager',
        'config' => array(
            'vendor-dir' => '.'
        )
    );

    /**
     * @var Core_Model_ModuleManager
     */
    protected $_moduleManager;
    
    public function setUp() {
        parent::setUp();
        
        Core_Model_Module_Registry::setInstance();
        Core_Model_DiFactory::reset();
        Core_Model_Dataprovider_DiFactory::reset();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');
        
        $this->_moduleManager = Core_Model_DiFactory::newModuleManager();
        $this->_moduleManager->setModulePath(APPLICATION_TEST_PATH . '/builds');
        
        $this->_composerMock = $this->getMock('Core_Model_Module_Composer', array('removeModule', 'setModule', 'install', 'isValid', 'update'), array($this->_moduleManager->getModulePath()));
        $this->_composerMock->expects($this->any())->method('setModule')->will($this->returnValue('true'));
        $this->_composerMock->expects($this->any())->method('install')->will($this->returnValue('true'));
        $this->_composerMock->expects($this->any())->method('isValid')->will($this->returnValue('true'));
        $this->_composerMock->expects($this->any())->method('update')->will($this->returnValue('true'));
        $this->_composerMock->expects($this->any())->method('updateModule')->will($this->returnValue('true'));
        $this->_composerMock->expects($this->any())->method('removeModule')->will($this->returnValue('true'));
        
        $this->_moduleManager->setComposer($this->_composerMock);
    }
    
    public function testGetInstalledModulesShouldReturnArray()
    {
        $this->assertInternalType('array', $this->_moduleManager->getInstalledModules());
    }
    
    public function testGetAvailableModulesShouldReturnArray()
    {
        $this->assertInternalType('array', $this->_moduleManager->getAvailableModules());
    }
    
    public function testGetModuleAsArrayShouldReturnEmptyArrayOnNonExistingModule()
    {
        $module = $this->_moduleManager->getModuleAsArray('notExistingModule');
        $this->assertEquals(array(), $module);
    }
    
    public function testGetModuleAsArrayShouldReturnFullArrayOnExistingModule()
    {
        $module = $this->_moduleManager->getModuleAsArray('sampleModule1');
        
        $this->assertInternalType('array', $module);
        $this->assertNotEmpty($module);
    }
    
    public function testGetModuleShouldReturnNullOnNonExistingModule()
    {
        $module = $this->_moduleManager->getModule('notExistingModule');
        $this->assertNull($module);
    }
    
    public function testGetModuleShouldReturnModuleObjectOnExistingModule()
    {
        $module = $this->_moduleManager->getModule('sampleModule1');
        $this->assertInstanceOf('Core_Model_ValueObject_Module', $module);
    }
    
    public function testGetInstalledModulesShouldGetModulesFromRegistryAndReturnsArrayWith3Entries()
    {
        $module1 = Core_Model_DiFactory::newModule(1);
        $module2 = Core_Model_DiFactory::newModule(2);
        $module3 = Core_Model_DiFactory::newModule(3);
        
        $moduleRegistry = $this->getMock('Core_Model_Module_Registry', array('getModules'));
        $moduleRegistry->expects($this->once())->method('getModules')
             ->will($this->returnValue(array(1 => $module1, 2 => $module2, 3 => $module3)));
        Core_Model_Module_Registry::setInstance($moduleRegistry);
        
        $modules = $this->_moduleManager->getInstalledModules();
        $this->assertInternalType('array', $modules);
        $this->assertEquals(count($modules), 3);
    }
    
    public function testGetAvailableModulesShouldReturnArrayWith8Entries()
    {
        $modules = $this->_moduleManager->getAvailableModules();
        $this->assertInternalType('array', $modules);
        $this->assertEquals(count($modules), 8);
    }
    
    public function testGetUpdateableModulesShouldReturnArray1Entries()
    {
        $modules = $this->_moduleManager->getUpdateableModules();
        $this->assertInternalType('array', $modules);
        $this->assertCount(1, $modules);
    }
    
    public function testInstallModuleWithNonExistentModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_moduleManager->installModule('notExisting'));
    }
    
    public function testInstallModuleShouldReturnTrue()
    {
        $this->assertTrue($this->_moduleManager->installModule('sampleModule1'));
    }
    
    public function testInstallModuleWithInstallFlagedModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_moduleManager->installModule('sampleModule2'));
    }
    
    public function testInstallValidModuleShouldCallComposerSetModule()
    {
        $this->_composerMock->expects($this->once())->method('setModule')->will($this->returnValue(true));
        $this->_moduleManager->setComposer($this->_composerMock);
        
        $this->_moduleManager->installModule('sampleModule1');
    }
    
    public function testAddValidModuleShouldReturnTrue()
    {
        $this->assertTrue($this->_moduleManager->addModule($this->_sample1));
    }
    
    public function testAddModuleShouldAddModuleToDataBackend()
    {
        $this->assertNull($this->_moduleManager->getModule('sampleAddModule1'));
        
        $this->_moduleManager->addModule($this->_sample1);
        
        $this->assertNotNull($this->_moduleManager->getModule('sampleAddModule1'));
    }
    
    public function testAddInvalidModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_moduleManager->addModule($this->_sampleInvalid));
    }
    
    public function testAddInvalidModuleShouldNotAddModuleToDataBackend()
    {
        $this->_moduleManager->addModule($this->_sampleInvalid);
        
        $this->assertNull($this->_moduleManager->getModule('sampleInvalid'));
    }
    
    public function testDeinstallModuleWithNotInstalledModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_moduleManager->deinstallModule('sampleModule1'));
    }
    
    public function testDeinstallModuleWithNotExistentModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_moduleManager->deinstallModule('notExistent'));
    }
    
    public function testDeinstallModuleShouldReturnTrue()
    {
        $this->_moduleManager->installModule('sampleModule1');
        $this->assertTrue($this->_moduleManager->deinstallModule('sampleModule1'));
    }
    
    public function testDeinstallModuleShouldCallComposerRemoveModule()
    {
        $this->_composerMock->expects($this->once())->method('removeModule')->will($this->returnValue(true));
        $this->_moduleManager->setComposer($this->_composerMock);
        
        $this->_moduleManager->installModule('sampleModule1');
        $this->_moduleManager->deinstallModule('sampleModule1');
    }
    
    public function testDeleteModuleShouldRemoveModuleInDataBackend()
    {
        $this->assertNotNull($this->_moduleManager->getModule('sampleModule1'));
        
        $this->_moduleManager->deleteModule('sampleModule1');
        
        $this->assertNull($this->_moduleManager->getModule('sampleModule1'));
    }
    
    public function testAddValidModuleWithSourcePathShouldReturnTrue()
    {
        $this->assertTrue($this->_moduleManager->addModule($this->_sample2));
    }
    
    public function testInstallModuleShouldAddInstallFlagToModuleData()
    {
        $module = $this->_moduleManager->getModule('sampleModule1');
        
        $this->assertNull($module->getData('installed'));
        
        $this->_moduleManager->installModule('sampleModule1');
        
        $this->assertTrue($module->getData('installed'));
    }
    
    public function testDeinstallModuleShouldSetInstalledFlagToFalse()
    {
        $this->_moduleManager->installModule('sampleModule1');
        $this->assertTrue($this->_moduleManager->getModule('sampleModule1')->getData('installed'));
        
        $this->_moduleManager->deinstallModule('sampleModule1');
        $this->assertNull($this->_moduleManager->getModule('sampleModule1')->getData('installed'));
    }
    
    public function testUpdateModuleShouldReturnFalseOnNotExistingModule()
    {
        $this->assertFalse($this->_moduleManager->updateModule('nonExistingModule'));
    }
    
    public function testUpdateModuleShouldReturnFalseWhenComposerSetModuleFailed()
    {
        $module = Core_Model_DiFactory::getModuleManager()->getModule('sampleModule3');
        $moduleRegistry = $this->getMock('Core_Model_Module_Registry', array('getModule'));
        $moduleRegistry->expects($this->once())->method('getModule')->will($this->returnValue($module));
        Core_Model_Module_Registry::setInstance($moduleRegistry);
        
        $this->_composerMock = $this->getMock('Core_Model_Module_Composer', array('removeModule', 'setModule', 'install', 'isValid', 'update'), array($this->_moduleManager->getModulePath()));
        $this->_composerMock->expects($this->once())->method('setModule')->will($this->returnValue(false));
        $this->_moduleManager->setComposer($this->_composerMock);
        
        $this->assertFalse($this->_moduleManager->updateModule('sampleModule3'));
    }
    
    public function testUpdateModuleShouldCallComposerSetModule()
    {
        $module = Core_Model_DiFactory::getModuleManager()->getModule('sampleModule3');
        $moduleRegistry = $this->getMock('Core_Model_Module_Registry', array('getModule'));
        $moduleRegistry->expects($this->once())->method('getModule')->will($this->returnValue($module));
        Core_Model_Module_Registry::setInstance($moduleRegistry);
        
        $this->_composerMock = $this->getMock('Core_Model_Module_Composer', array('removeModule', 'setModule', 'install', 'isValid', 'update'), array($this->_moduleManager->getModulePath()));
        $this->_composerMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->_composerMock->expects($this->once())->method('setModule')->will($this->returnValue(true));
        $this->_moduleManager->setComposer($this->_composerMock);
        
        $this->_moduleManager->updateModule('sampleModule3');
    }
    
    public function testUpdateModuleShouldRemoveUpdateDataSetWhenSuccessful()
    {
        
        $moduleRegistry = $this->getMock('Core_Model_Module_Registry', array('getModule'));
        $moduleRegistry->expects($this->once())->method('getModule')
             ->will($this->returnValue($this->_moduleManager->getModule('sampleModule3')));
        Core_Model_Module_Registry::setInstance($moduleRegistry);
        
        $this->assertTrue($this->_moduleManager->updateModule('sampleModule3'));
        $this->assertNull($this->_moduleManager->getModule('sampleModule3')->getData('updateable'));
        $this->assertNull($this->_moduleManager->getModule('sampleModule3')->getData('update'));
    }
    
    public function testAddModuleWithInvalidVersionShouldReturnFalse()
    {
        $this->assertFalse($this->_moduleManager->addModule($this->_sampleInvalid2));
    }
    
    public function testDeinstallModuleOnUpdateableModuleShouldSetTheNewVersion()
    {
        $this->assertInternalType('array', $this->_moduleManager->getModule('sampleModule3')->getData('update'));
        $this->_moduleManager->deinstallModule('sampleModule3');
        $this->assertNull($this->_moduleManager->getModule('sampleModule3')->getData('update'));
    }
    
    public function testDeinstallModuleShouldNotDeleteModuleInDataBackend()
    {
        $this->_moduleManager->installModule('sampleModule1');
        $this->_moduleManager->deinstallModule('sampleModule1');
        
        $this->assertNotNull($this->_moduleManager->getModule('sampleModule1'));
    }
    
    public function testDeinstallModuleWithoutRepositoryDefinitionShouldReturnTrue()
    {
        $this->assertTrue($this->_moduleManager->deinstallModule('sampleModule7'));
    }
    
    public function testAddAdditionalFieldShouldReturnTNotFalse()
    {
        $this->assertNotNull($this->_moduleManager->addAdditionalField('sampleModule2', $this->_additionalField));
    }

    public function testAddAdditionalFieldWithValidDataShouldCallSave()
    {
        $module = $this->getMock('Core_Model_ValueObject_Module', array('save'), array('sampleModule3'));
        $module->expects($this->once())
               ->method('save')->will($this->returnValue(true));

        Core_Model_DiFactory::registerModule('sampleModule3', $module);

        $this->_moduleManager->addAdditionalField('sampleModule3', $this->_additionalField);
    }

    public function testAddAdditionalFieldShouldReturnFalseWithNoneExistsParent()
    {
        $this->assertFalse($this->_moduleManager->addAdditionalField(null, $this->_additionalField));
    }

    public function testAddAdditionalFieldWhitValidDatasetShoulNotCallSave()
    {
        $object = $this->getMock('Core_Model_ValueObject_Module', array('save'), array('sampleModule4'));
        $object->expects($this->never())
               ->method('save');

       $this->_moduleManager->addAdditionalField('sampleModule4', $this->_additionalField);
    }

    public function testAddAdditionalFieldShouldReturnMd5edKey()
    {
        $this->assertEquals(md5('foo'), $this->_moduleManager->addAdditionalField('sampleModule2', $this->_additionalField));
    }

    public function testDeleteAdditionalFieldShouldReturnTrue()
    {
        $this->_moduleManager->addAdditionalField('sampleModule2', $this->_additionalField);

        $this->assertTrue($this->_moduleManager->deleteAdditionalField('sampleModule2', 'foo'));
    }

    public function testDeleteAdditionalFieldShouldReturnFalseOnNoneExistsField()
    {
        $this->_moduleManager->addAdditionalField('sampleModule5', $this->_additionalField);
        $this->assertTrue($this->_moduleManager->deleteAdditionalField('sampleModule5', 'baz'));
    }
}
