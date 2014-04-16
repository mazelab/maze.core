<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ClientManagerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ClientManagerTest extends PHPUnit_Framework_TestCase
{
    const INDEX = "additionalKey";
    const VALUE = "additionalValue";

    /**
     * @var Core_Model_ClientManager
     */
    private $manager;

    /**
     * @var array
     */
    private $values = array(self::INDEX => "foo", self::VALUE => "bar");

    public function setUp()
    {
        parent::setUp();
        Core_Model_Dataprovider_DiFactory::setAdapter('Demo');

        $this->manager = Core_Model_DiFactory::newClientManager();
    }

    public function testAddAdditionalFieldShouldReturnTNotFalse()
    {
        $this->assertNotNull($this->manager->addAdditionalField('clientSample1', $this->values));
    }

    public function testAddAdditionalFieldWithValidDataShouldCallSave()
    {
        $client = $this->getMock('Core_Model_ValueObject_Client', array('save'), array('clientSample2'));
        $client->expects($this->once())
               ->method('save')->will($this->returnValue(true));

        Core_Model_DiFactory::registerClient('clientSample2', $client);

        $this->manager->addAdditionalField('clientSample2', $this->values);
    }

    public function testAddAdditionalFieldShouldReturnFalseWithNoneExistsParent()
    {
        $this->assertFalse($this->manager->addAdditionalField(null, $this->values));
    }

    public function testAddAdditionalFieldWhitValidDatasetShoulNotCallSave()
    {
        $object = $this->getMock('Core_Model_ValueObject_Client', array('save'), array('clientSample1'));
        $object->expects($this->never())
               ->method('save');

       $this->manager->addAdditionalField('clientSample1', $this->values);
    }

    public function testAddAdditionalFieldShouldReturnMd5edKey()
    {
        $this->assertEquals(md5('foo'), $this->manager->addAdditionalField('clientSample1', $this->values));
    }

    public function testDeleteAdditionalFieldShouldReturnTrue()
    {
        $this->manager->addAdditionalField('clientSample1', $this->values);

        $this->assertTrue($this->manager->deleteAdditionalField('clientSample1', 'foo'));
    }

    public function testDeleteAdditionalFieldShouldReturnFalseOnNoneExistsField()
    {
        $this->manager->addAdditionalField('clientSample1', $this->values);

        $this->assertTrue($this->manager->deleteAdditionalField('clientSample1', 'foo'));
    }
}
