<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_LoggerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_LoggerTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @var Core_Model_Logger
     */
    protected $_logger;
    
    /**
     * @var array
     */
    protected $_client = array(
        '_id' => '123abc456def',
        'label' => 'phpunit bro',
        'username' => 'testclient1'
    );
    
    /**
     * @var array
     */
    protected $_admin = array(
        '_id' => 'fed654cba321',
        'label' => 'just admin..',
        'username' => 'testadmin1',
        'group' => Core_Model_UserManager::GROUP_ADMIN
    );
    
    public function setUp() {
        parent::setUp();
        
        $this->_logger = Core_Model_DiFactory::newLogger();
        
        Core_Model_DiFactory::reset();
        Core_Model_Dataprovider_DiFactory::reset();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');
    }
    
    public function tearDown() {
        parent::tearDown();
        
        Zend_Auth::getInstance()->clearIdentity();
    }
    
    public function testGetIdentityLabelWithoutRealIdentityShouldReturnDefaultUser()
    {
        $this->assertEquals(Core_Model_Logger::SYSTEM_USER, $this->_logger->getIdentityLabel());
    }
    
    public function testGetIdentityLabelWithClientShouldReturnLabelProperty()
    {
        $client = Core_Model_DiFactory::newClient($this->_client['_id']);
        $client->setData($this->_client);
        
        Core_Model_DiFactory::registerClient($this->_client['_id'], $client);
        Zend_Auth::getInstance()->getStorage()->write($this->_client);

        $this->assertEquals($this->_client['label'], $this->_logger->getIdentityLabel());
    }
    
    public function testGetIdentityLabelWithAdminShouldReturnUsernameProperty()
    {
        $admin = Core_Model_DiFactory::newAdmin($this->_admin['_id']);
        $admin->setData($this->_admin);
        
        Core_Model_DiFactory::registerAdmin($this->_admin['_id'], $admin);
        Zend_Auth::getInstance()->getStorage()->write($this->_admin);

        $this->assertEquals($this->_admin['username'], $this->_logger->getIdentityLabel());
    }
    
    public function testGetIdentityLabelWithAdminLoggedInAsClientShouldReturnAdminLabel()
    {
        $admin = Core_Model_DiFactory::newAdmin($this->_admin['_id']);
        $admin->setData($this->_admin);
        
        Core_Model_DiFactory::registerAdmin($this->_admin['_id'], $admin);
        
        $client = Core_Model_DiFactory::newClient($this->_client['_id']);
        $client->setData($this->_client);
        
        Core_Model_DiFactory::registerClient($this->_client['_id'], $client);
        
        $identity = $this->_client;
        $identity['adminUser'] = $this->_admin;
        
        Zend_Auth::getInstance()->getStorage()->write($identity);
        
        $this->assertEquals($this->_admin['username'], $this->_logger->getIdentityLabel());
    }
    
    public function testGetIdentityIdWithoutRealIdentityShouldReturnNull()
    {
        $this->assertNull($this->_logger->getIdentityId());
    }
    
    public function testGetIdentityWithRealIdentityShouldReturnUserId()
    {
        $client = Core_Model_DiFactory::newClient($this->_client['_id']);
        $client->setData($this->_client);
        
        Core_Model_DiFactory::registerClient($this->_client['_id'], $client);
        Zend_Auth::getInstance()->getStorage()->write($this->_client);
        
        $this->assertEquals($this->_client['_id'], $this->_logger->getIdentityId());
    }
    
    public function testSaveWithNothingSetShouldReturnFalse()
    {
        $this->assertFalse($this->_logger->save());
    }
    
    public function testSaveWithPropertyRequirementsShouldReturnId()
    {
        $this->_logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message');
        
        $this->assertInternalType('string', $this->_logger->save());
    }
    
    public function testFailedSaveShouldResetLogger()
    {
        $logger = $this->getMock('Core_Model_Logger', array('reset'));
        $logger->expects($this->once())
                ->method('reset');
        
        $this->assertFalse($logger->save());
    }
    
    public function testSuccessfulSaveShouldResetLogger()
    {
        $logger = $this->getMock('Core_Model_Logger', array('reset'));
        $logger->expects($this->once())
                ->method('reset');
        
        $logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message');
        
        $this->assertInternalType('string', $logger->save());
    }
    
    public function testSaveByContextWithNothingSetShouldReturnFalse()
    {
        $this->assertFalse($this->_logger->saveByContext('3125687'));
    }
    
    public function testSaveByContextWithTypeAndActionWithNothingSetShouldReturnFalse()
    {
        $this->assertFalse($this->_logger->saveByContext('3125687', 'notify', 'test'));
    }
    
    public function testSaveByContextWithPropertyRequirementsShouldReturnTrue()
    {
        $this->_logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message')
                ->setAction('test');
        
        $this->assertTrue($this->_logger->saveByContext('3125687', 'notify', 'test'));
    }
    
    public function testSaveByContextWithoutTypeAndActionItShouldUseTheSetedPropertiesAndReturnTrue()
    {
        $this->_logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message')
                ->setAction('test');
        
        $this->assertTrue($this->_logger->saveByContext('3125687'));
    }
    
    public function testFailedSaveByContextShouldResetLogger()
    {
        $logger = $this->getMock('Core_Model_Logger', array('reset'));
        $logger->expects($this->once())
                ->method('reset');
        
        $this->assertFalse($logger->saveByContext('897465312'));
    }
    
    public function testSaveByContextWithoutActionSetShouldReturnFalse()
    {
        $this->_logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message');
        
        $this->assertFalse($this->_logger->saveByContext('3125687'));
    }
    
    public function testSuccessfulSaveByContextShouldResetLogger()
    {
        $logger = $this->getMock('Core_Model_Logger', array('reset'));
        $logger->expects($this->once())
                ->method('reset');
        
        $logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message')
                ->setAction('test');
        
        $this->assertTrue($logger->saveByContext('54689754312'));
    }
    
    public function testUpdateWithNothingSetShouldReturnFalse()
    {
        $this->assertFalse($this->_logger->update('update1'));
    }
    
    public function testUpdateWithPropertyRequirementsShouldReturnTrue()
    {
        $this->_logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message')
                ->setAction('test');
        
        $this->assertTrue($this->_logger->update('update1'));
    }
    
    public function testFailedUpdateShouldResetLogger()
    {
        $logger = $this->getMock('Core_Model_Logger', array('reset'));
        $logger->expects($this->once())
                ->method('reset');
        
        $this->assertFalse($logger->update('update1'));
    }
    
    public function testSuccessfulUpdateShouldResetLogger()
    {
        $logger = $this->getMock('Core_Model_Logger', array('reset'));
        $logger->expects($this->once())
                ->method('reset');
        
        $logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage('phpunit test message')
                ->setAction('test');
        
        $this->assertTrue($logger->update('update1'));
    }
    
}
