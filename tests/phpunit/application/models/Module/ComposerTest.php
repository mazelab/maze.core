<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_ComposerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_ComposerTest extends PHPUnit_Framework_TestCase
{

    /**
     * current instance of composer manager
     * 
     * @var Core_Model_Module_Composer
     */
    protected $_composerManager;
    
    /**
     * composer test directory
     * 
     * @var string
     */
    protected $_composerPath;
    
    /**
     * composer test file path
     * 
     * @var string
     */
    protected $_composerFilePath;
    
    /**
     * mocked Core_Model_Module_Composer instance
     * 
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_composerMock;
    
    /**
     * init test environment
     */
    public function setUp() {
        parent::setUp();

        Core_Model_Dataprovider_DiFactory::reset();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');
        
        $this->_composerPath = APPLICATION_TEST_PATH . '/builds';
        $this->_composerFilePath = APPLICATION_TEST_PATH . '/builds/composer.json';
        
        $this->_composerManager = new Core_Model_Module_Composer($this->_composerPath);
        $this->_composerManager->resetComposer();
        
        $this->_composerManager = new Core_Model_Module_Composer($this->_composerPath);
        
        $this->_composerMock = $this->getMock('Core_Service_Composer', array('install', 'update'));
        $this->_composerMock->expects($this->any())->method('install')->will($this->returnValue(true));
        $this->_composerMock->expects($this->any())->method('update')->will($this->returnValue(true));
        
        Core_Service_DiFactory::setComposer($this->_composerMock);
    }
    
    public function testConstructorShouldSetComposerPath()
    {
        $this->assertEquals($this->_composerPath, $this->_composerManager->getComposerPath());
    }
    
    public function testGetComposerFilePathShouldReturnCorrectPath()
    {
        $this->assertEquals($this->_composerFilePath, $this->_composerManager->getComposerFilePath());
    }
    
    public function testGetComposerFilePathWhenComposerPathNotSetShouldReturnNull()
    {
        $this->_composerManager->setComposerPath();
        $this->assertNull($this->_composerManager->getComposerFilePath());
    }
    
    public function testResetComposerShouldReturnTrue()
    {
        $this->assertTrue($this->_composerManager->resetComposer());
    }
    
    public function testResetComposerShouldCreateComposerJson()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->_composerManager->resetComposer();
        $this->assertFileExists($this->_composerFilePath);
    }
    
    public function testGetComposerShouldReturnInstanceOfZendConfig()
    {
        $this->assertInstanceOf('Zend_Config', $this->_composerManager->getComposer());
    }
    
    public function testGetComposerOnNonExistingComposerJsonShouldReturnInitialSettings()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $composer = $this->_composerManager->getComposer();
        $this->assertInstanceOf('Zend_Config', $composer);
        $this->assertNotEmpty($composer->toArray());
        
        $this->assertInternalType('string', $composer->name);
        $this->assertInternalType('string', $composer->description);
    }
    
    public function testGetComposerAsArrayShouldReturnAFilledArray()
    {
        $this->assertInternalType('array', $this->_composerManager->getComposerAsArray());
        $this->assertNotEmpty($this->_composerManager->getComposerAsArray());
    }
    
    public function testGetComposerAsArrayOnNonExistingComposerJsonShouldReturnInitialSettings()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertInternalType('array', $composer);
        $this->assertNotEmpty($composer);
        
        $this->assertArrayHasKey('name', $composer);
        $this->assertArrayHasKey('description', $composer);
    }
    
    public function testResetComposerShouldCreateValidComposerJson()
    {
        $this->_composerManager->resetComposer();
        $this->assertTrue($this->_composerManager->isValid());
    }
    
    public function testSetModuleShouldReturnTrue()
    {
        $this->assertTrue($this->_composerManager->setModule('sampleModule1'));
    }
    
    public function testSetModuleShouldCreateNewComposerJsonIfNotExisting()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->_composerManager->setModule('sampleModule1');
        $this->assertFileExists($this->_composerFilePath);
    }
    
    public function testSetModuleShouldCreateNewComposerJsonWithInitialSettings()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->_composerManager->setModule('sampleModule1');
        
        $composer = $this->_composerManager->getComposer();
        $this->assertInstanceOf('Zend_Config', $composer);
        $this->assertNotEmpty($composer->toArray());
        
        $this->assertInternalType('string', $composer->name);
        $this->assertInternalType('string', $composer->description);
    }
    
    public function testSetModuleShouldSetRequireEntries()
    {
        $module = Core_Model_DiFactory::getModuleManager()->getModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule1');
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertArrayHasKey($module->getData('repository/name'), $composer['require']);
        $this->assertEquals($module->getData('repository/version'), $composer['require'][$module->getData('repository/name')]);
    }
    
    public function testSetModuleShouldSetRepositoryEntries()
    {
        $module = Core_Model_DiFactory::getModuleManager()->getModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule1');
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertArrayHasKey(0, $composer['repositories']);
        $this->assertEquals($module->getData('repository/url'), $composer['repositories'][0]['url']);
        $this->assertEquals($module->getData('repository/type'), $composer['repositories'][0]['type']);
    }
    
    public function testSetModuleWith3ModulesShouldSet3RequireEntries()
    {
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule2');
        $this->_composerManager->setModule('sampleModule3');

        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertCount(3, $composer['require']);
    }
    
    public function testSetModuleWith3ModulesShouldSet3RepositoryEntries()
    {
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule2');
        $this->_composerManager->setModule('sampleModule3');

        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertCount(3, $composer['repositories']);
    }
    
    public function testAddSameModuleMultipleTimesModuleShouldSet1RepositoryAnd1Require()
    {
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule1');

        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertCount(1, $composer['repositories']);
        $this->assertCount(1, $composer['require']);
    }
    
    public function testSetModuleWithUpdatedUrlShouldAddNewRepository()
    {
        $this->_composerManager->setModule('sampleModule1');
        
        $module = Core_Model_DiFactory::getModuleManager()->getModule('sampleModule1');
        $module->setProperty('repository/url', 'http://verynewprojecturl/here');
        $module->setProperty('repository/version', '10.0.1');
        
        $this->_composerManager->setModule('sampleModule1');
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertEquals('http://verynewprojecturl/here', $composer['repositories'][1]['url']);
        $this->assertEquals('10.0.1', $composer['require'][$module->getData('repository/name')]);
    }
    
    public function testSetModuleShouldReturnFalseWhenComposerUpdateFailed()
    {
        $this->_composerMock = $this->getMock('Core_Service_Composer', array('install', 'update'));
        $this->_composerMock->expects($this->once())->method('update')->will($this->returnValue(false));
        
        Core_Service_DiFactory::setComposer($this->_composerMock);
        
        $this->assertFalse($this->_composerManager->setModule('sampleModule1'));
    }
    
    public function testSetModuleShouldRevertComposerSettingsIfComposerUpdateFailed()
    {
        $this->_composerMock = $this->getMock('Core_Service_Composer', array('install', 'update'));
        $this->_composerMock->expects($this->once())->method('update')->will($this->returnValue(false));
        
        Core_Service_DiFactory::setComposer($this->_composerMock);
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->_composerManager->setModule('sampleModule1');
        
        $this->assertEquals($composer, $this->_composerManager->getComposerAsArray());
    }
    
    public function testInstallShouldReturnTrue()
    {
        $mock = $this->getMock('Core_Service_Composer', array('install', 'update', 'validate'));
        $mock->expects($this->once())->method('install')->will($this->returnValue(true));
        Core_Service_DiFactory::setComposer($mock);
        
        $this->assertTrue($this->_composerManager->install());
    }
    
    public function testInstallShouldReturnFalseIfComposerFileDoesNotExist()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->assertFalse($this->_composerManager->install());
    }
    
    public function testInstallShouldCallServiceComposerInstall()
    {
        $mock = $this->getMock('Core_Service_Composer', array('install', 'update', 'validate'));
        $mock->expects($this->once())->method('install')->will($this->returnValue(true));
        Core_Service_DiFactory::setComposer($mock);
        
        $this->assertTrue($this->_composerManager->install());
    }
    
    public function testUpdateShouldReturnTrue()
    {
        $mock = $this->getMock('Core_Service_Composer', array('install', 'update', 'validate'));
        $mock->expects($this->any())->method('update')->will($this->returnValue(true));
        Core_Service_DiFactory::setComposer($mock);
        
        $this->assertTrue($this->_composerManager->update());
    }
    
    public function testUpdateShouldReturnFalseIfComposerFileDoesNotExist()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->assertFalse($this->_composerManager->update());
    }
    
    public function testUpdateModuleShouldReturnTrue()
    {
        $mock = $this->getMock('Core_Service_Composer', array('install', 'update', 'validate'));
        $mock->expects($this->any())->method('update')->will($this->returnValue(true));
        Core_Service_DiFactory::setComposer($mock);
        
        $this->assertTrue($this->_composerManager->updateModule('sampleModule1'));
    }
    
    public function testUpdateModuleShouldReturnFalseIfComposerFileDoesNotExist()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->assertFalse($this->_composerManager->updateModule('sampleModule1'));
    }
    
    public function testUpdateModuleShouldCallServiceComposerUpdate()
    {
        $mock = $this->getMock('Core_Service_Composer', array('install', 'update', 'validate'));
        $mock->expects($this->once())->method('update')->will($this->returnValue(true));
        Core_Service_DiFactory::setComposer($mock);
        
        $this->_composerManager->updateModule('sampleModule1');
    }
    
    public function testUpdateModuleWithNonExistentModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_composerManager->updateModule('nonExistentModule'));
    }
    
    public function testUpdateModuleWithModuleWithoutRepositoryShouldReturnFalse()
    {
        $this->assertFalse($this->_composerManager->updateModule('sampleModule7'));
    }
    
    public function testRemoveModuleShouldReturnTrue()
    {
        $this->assertTrue($this->_composerManager->removeModule('sampleModule1'));
    }

    public function testRemoveModuleWithoutComposerFileModuleShouldReturnFalse()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->assertFalse($this->_composerManager->removeModule('sampleModule1'));
    }
    
    public function testRemoveModuleWithNonExistingModuleShouldReturnFalse()
    {
        $this->assertFalse($this->_composerManager->removeModule('nonexistingmodule'));
    }
    
    public function testRemoveModuleWithModuleWithoutRepositoryShouldReturnFalse()
    {
        $this->assertFalse($this->_composerManager->removeModule('sampleModule7'));
    }
    
    public function testRemoveModuleShouldRemoveRequireAndRepositoryFieldsCompletely()
    {
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->removeModule('sampleModule1');
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertArrayNotHasKey('require', $composer);
        $this->assertArrayNotHasKey('repositories', $composer);
    }
    
    public function testRemoveModuleShouldOnlyRemoveRequireAndRepositoryFieldsOfTheGivenModule()
    {
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule2');
        $this->_composerManager->setModule('sampleModule3');
        $this->_composerManager->removeModule('sampleModule1');
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertCount(2, $composer['require']);
        $this->assertCount(2, $composer['repositories']);
    }
    
    public function testRemoveModuleShouldResetComposerSettingsIfComposerUpdateFailed()
    {
        $mock = $this->getMock('Core_Service_Composer', array('install', 'update', 'validate'));
        $mock->expects($this->any())->method('update')->will($this->returnValue(false));
        Core_Service_DiFactory::setComposer($mock);
        
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule2');

        $composer = $this->_composerManager->getComposerAsArray();
        $this->_composerManager->removeModule('sampleModule1');
        $this->assertEquals($composer, $this->_composerManager->getComposerAsArray());
    }
    
    public function testRemoveModuleMultipleTimeShouldOnlyRemove1Entry()
    {
        $this->_composerManager->setModule('sampleModule1');
        $this->_composerManager->setModule('sampleModule2');
        $this->_composerManager->setModule('sampleModule3');
        $this->_composerManager->removeModule('sampleModule1');
        $this->_composerManager->removeModule('sampleModule1');
        $this->_composerManager->removeModule('sampleModule1');
        $this->_composerManager->removeModule('sampleModule1');
        
        $composer = $this->_composerManager->getComposerAsArray();
        $this->assertCount(2, $composer['require']);
        $this->assertCount(2, $composer['repositories']);
    }
    
    public function testIsValidShouldReturnTrue()
    {
        $this->assertTrue($this->_composerManager->isValid());
    }
    
    public function testIsValidNonStrictShouldCreateComposerJsonIfMissingAndReturnTrue()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->assertTrue($this->_composerManager->isValid());
        $this->assertFileExists($this->_composerFilePath);
    }
    
    public function testIsValidStrictShouldReturnFalseWhenComposerJsonIsMissing()
    {
        if(file_exists($this->_composerFilePath)) {
            unlink($this->_composerFilePath);
        }
        
        $this->assertFalse($this->_composerManager->isValid(true));
    }
    
}
