<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_RegistryTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_RegistryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_samplePath1 = '/samples/modules/configs/sample1.ini';
    
    /**
     * @var string
     */
    protected $_samplePath2 = '/samples/modules/configs/sample2.ini';
    
    /**
     * @var string
     */
    protected $_samplePath3 = '/samples/modules/configs/sample3.ini';
    
    /**
     * @var string
     */
    protected $_samplePath4 = '/samples/modules/configs/sample4.ini';
    
    /**
     * @var string
     */
    protected $_samplePath5 = '/samples/modules/configs/sample5.ini';
    
    public function setUp() {
        parent::setUp();

        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');
        
        Core_Model_DiFactory::setModuleManager();
        Core_Model_DiFactory::getModuleRegistry()->setInstance();
    }
    
    public function testRegisterNewValidModuleShouldReturnTrue()
    {
        $this->assertTrue(Core_Model_DiFactory::getModuleRegistry()
                ->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1));
    }
    
    public function testRegisterNewValidModuleShouldRunValidateModule()
    {
        $moduleRegistry = $this->getMock('Core_Model_Module_Registry', array('_validateConfig'));
        $moduleRegistry->expects($this->once())->method('_validateConfig')
                ->will($this->returnValue(true));

        $moduleRegistry->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
    }
    
    public function testRegisterNewValidModuleShouldRunLoadConfig()
    {
        $config = new Zend_Config_Ini(APPLICATION_TEST_PATH . $this->_samplePath1);
        
        $moduleRegistry = $this->getMock('Core_Model_Module_Registry', array('_loadConfig'));
        $moduleRegistry->expects($this->once())->method('_loadConfig')
                ->will($this->returnValue($config->toArray()));

        $moduleRegistry->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
    }

    /**
     * @expectedException Core_Model_Module_Exception
     */
    public function testRegisterInvalidConfigPathShouldThrowException()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule('notExistingPath');
    }
    
    /**
     * @expectedException Zend_Config_Exception
     */
    public function testRegisterInvalidConfigPathFormatShouldThrowException()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath5);
    }
    
    /**
     * @expectedException Core_Model_Module_Exception
     */
    public function testRegisterInvalidModuleShouldThrowException()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath4);
    }
    
    /**
     * @expectedException Core_Model_Module_Exception
     */
    public function testRegister2SameValidModulesShouldThrowException()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
    }
    
    public function testRegisterValidModuleShouldCallRegisterModuleOfModuleManager()
    {
        $moduleManager = $this->getMock('Core_Model_ModuleManager', array('registerModule'));
        $moduleManager->expects($this->once())->method('registerModule');
        
        Core_Model_DiFactory::setModuleManager($moduleManager);
        
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
    }
    
    public function testGetModuleWithNotExistentModuleShouldReturnNull()
    {
        $this->assertNull(Core_Model_DiFactory::getModuleRegistry()->getModule('notExistent'));
    }
    
    public function testGetModuleWithNullShouldReturnNull()
    {
        $this->assertNull(Core_Model_DiFactory::getModuleRegistry()->getModule(null));
    }
    
    public function testGetModuleWithExistingModuleShouldReturnModuleValueObject()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
        $this->assertInstanceOf('Core_Model_ValueObject_Module',
                Core_Model_DiFactory::getModuleRegistry()->getModule('validServiceSample1'));
    }
    
    public function testGetModulesWithoutInitializedModulesShouldReturnEmptyArray()
    {
        $this->assertInternalType('array', Core_Model_DiFactory::getModuleRegistry()->getModules());
        $this->assertEmpty(Core_Model_DiFactory::getModuleRegistry()->getModules());
    }
    
    public function testGetModulesWithInitializedModulesShouldReturnArray()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
        
        $this->assertInternalType('array', Core_Model_DiFactory::getModuleRegistry()->getModules());
    }
    
    public function testGetModulesShouldReturnOneModule()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
        
        $this->assertCount(1, Core_Model_DiFactory::getModuleRegistry()->getModules());
    }
    
    public function testGetModulesShouldReturnThreeModules()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath1);
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath2);
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath3);
        
        $this->assertCount(3, Core_Model_DiFactory::getModuleRegistry()->getModules());
    }
    
    public function testUnregisterModuleWithNotExistentModuleShouldReturnFalse()
    {
        $this->assertFalse(Core_Model_DiFactory::getModuleRegistry()->unregisterModule('notExistent'));
    }
    
    public function testUnregisterModuleWithExistentModuleShouldReturnTrue()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath3);
        
        $this->assertTrue(Core_Model_DiFactory::getModuleRegistry()->unregisterModule('validServiceSample3'));
    }
    
    public function testUnregisterModuleWithExistitentModuleShouldUnsetModule()
    {
        Core_Model_DiFactory::getModuleRegistry()->registerModule(APPLICATION_TEST_PATH . $this->_samplePath2);
        
        $this->assertNotNull(Core_Model_DiFactory::getModuleRegistry()->getModule('validServiceSample2'));
        Core_Model_DiFactory::getModuleRegistry()->unregisterModule('validServiceSample2');
        $this->assertNull(Core_Model_DiFactory::getModuleRegistry()->getModule('validServiceSample2'));
    }
    
}
