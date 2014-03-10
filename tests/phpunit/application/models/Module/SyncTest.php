<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_SyncTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_SyncTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @var Core_Model_Module_Sync
     */
    protected $_moduleSync;
    
    /**
     * @var string
     */
    protected $_sampleValidJson1 = '[{
        "name" : "foo",
        "label" : "Foo",
        "vendor" : "sample",
        "description" : "sample module",
        "repository" : [{
            "name" : "mazefoo",
            "url" : "https://sample.vendor/foo",
            "type" : "vcs",
            "version" : "0.0.1"
        }]
    }]';
    
    /**
     * @var string
     */
    protected $_sampleValidJson2 = '[{
        "name"  : "bar",
        "label" : "Bar",
        "vendor" : "sample",
        "description" : "sample module",
        "repository" : [{
            "url" : "http://sample.vendor/bar",
            "type" : "vcs",
            "version" : "0.0.6",
            "name" : "bar"
        }]
    }]';
    
    /**
     * @var string
     */
    protected $_sampleInvalidJson = '<head></head><body>[{"name" : "testing"}]</body>';
    
    public function setUp() {
        parent::setUp();
        
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');
        Core_Model_DiFactory::reset();
        
        $this->_moduleSync = Core_Model_DiFactory::getModuleSync();
    }
    
    public function testSyncWithEmptyModuleDataShouldReturnFalse()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_loadJson'));
        $moduleSync->expects($this->once())
                    ->method('_loadJson')
                    ->will($this->returnValue(null));
        
        $this->assertFalse($moduleSync->sync());
    }
    
    public function testSyncWithInvalidJsonResponseShouldReturnFalse()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_loadJson'));
        $moduleSync->expects($this->once())
                    ->method('_loadJson')
                    ->will($this->returnValue($this->_sampleInvalidJson));
        
        $this->assertFalse($moduleSync->sync());
    }
    
    public function testSyncWithValidJsonResponseShouldReturnTrue()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_loadJson'));
        $moduleSync->expects($this->once())
                    ->method('_loadJson')
                    ->will($this->returnValue($this->_sampleValidJson1));
        
        $this->assertTrue($moduleSync->sync());
    }
    
    public function testSyncWithNewModuleShouldCallModuleManagerToAddModule()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_loadJson'));
        $moduleSync->expects($this->once())->method('_loadJson')
                    ->will($this->returnValue($this->_sampleValidJson1));
        
        $moduleManager = $this->getMock('Core_Model_ModuleManager', array('addModule'));
        $moduleManager->expects($this->once())->method('addModule');
        Core_Model_DiFactory::setModuleManager($moduleManager);
        
        $moduleSync->sync();
    }
    
    public function testSyncWithExistingModuleShouldUseModuleObjectToUpdateModule()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_loadJson'));
        $moduleSync->expects($this->once())->method('_loadJson')
                    ->will($this->returnValue($this->_sampleValidJson2));

        $module = $this->getMock('Core_Model_ValueObject_Module', array('syncModuleUpdate'), array('bar'));
        $module->expects($this->once())->method('syncModuleUpdate');
        Core_Model_DiFactory::registerModule('bar', $module);
        
        $moduleSync->sync();
    }
    
    public function testSyncDailyOnTheSameDayShouldntCallSyncScriptAndReturnsTrue()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_syncScript'));
        $moduleSync->expects($this->never())
                    ->method('_syncScript')
                    ->will($this->returnValue(true));
        
        $config = Core_Model_DiFactory::getConfig()->setData(array('lastModuleSync' => time()));
        Zend_Registry::getInstance()->set('mazeConfig', $config);
        
        $this->assertTrue($moduleSync->syncDaily());
    }
    
    public function testSyncDailyOnNotTodayShouldCallSyncScriptAndReturnsTrue()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_syncScript'));
        $moduleSync->expects($this->once())
                    ->method('_syncScript')
                    ->will($this->returnValue(true));
        
        $config = Core_Model_DiFactory::getConfig()->setData(array('lastModuleSync' => 2));
        Zend_Registry::getInstance()->set('mazeConfig', $config);
        
        $this->assertTrue($moduleSync->syncDaily());
    }
    
    public function testSyncDailyShouldReturnFalseIfSyncScriptReturnedFalse()
    {
        $moduleSync = $this->getMock('Core_Model_Module_Sync', array('_syncScript'));
        $moduleSync->expects($this->once())
                    ->method('_syncScript')
                    ->will($this->returnValue(false));
        
        $config = Core_Model_DiFactory::getConfig()->setData(array('lastModuleSync' => 2));
        Zend_Registry::getInstance()->set('mazeConfig', $config);
        
        $this->assertFalse($moduleSync->syncDaily());
   }
    
}
