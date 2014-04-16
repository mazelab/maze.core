<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_NodeManagerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_NodeManagerTest extends PHPUnit_Framework_TestCase
{
    const INDEX = "additionalKey";
    const VALUE = "additionalValue";

    /**
     * @var Core_Model_NodeManager
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

        $this->manager = Core_Model_DiFactory::newNodeManager();
    }

    public function testAddAdditionalFieldShouldReturnTNotFalse()
    {
        $this->assertNotNull($this->manager->addAdditionalField('nodeSample1', $this->values));
    }

    public function testAddAdditionalFieldWithValidDataShouldCallSave()
    {
        $node = $this->getMock('Core_Model_ValueObject_Node', array('save'), array('nodeSample2'));
        $node->expects($this->once())
               ->method('save')->will($this->returnValue(true));

        Core_Model_DiFactory::registerNode('nodeSample2', $node);

        $this->manager->addAdditionalField('nodeSample2', $this->values);
    }

    public function testAddAdditionalFieldShouldReturnFalseWithNoneExistsParent()
    {
        $this->assertFalse($this->manager->addAdditionalField(null, $this->values));
    }

    public function testAddAdditionalFieldWhitValidDatasetShoulNotCallSave()
    {
        $object = $this->getMock('Core_Model_ValueObject_Node', array('save'), array('nodeSample1'));
        $object->expects($this->never())
               ->method('save');

       $this->manager->addAdditionalField('nodeSample1', $this->values);
    }

    public function testAddAdditionalFieldShouldReturnMd5edKey()
    {
        $this->assertEquals(md5('foo'), $this->manager->addAdditionalField('nodeSample1', $this->values));
    }

    public function testDeleteAdditionalFieldShouldReturnTrue()
    {
        $this->manager->addAdditionalField('nodeSample1', $this->values);

        $this->assertTrue($this->manager->deleteAdditionalField('nodeSample1', 'foo'));
    }

    public function testDeleteAdditionalFieldShouldReturnFalseOnNoneExistsField()
    {
        $this->manager->addAdditionalField('nodeSample1', $this->values);

        $this->assertTrue($this->manager->deleteAdditionalField('nodeSample1', 'foo'));
    }
}
