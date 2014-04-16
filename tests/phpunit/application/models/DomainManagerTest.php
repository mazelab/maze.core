<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_DomainManagerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_DomainManagerTest extends PHPUnit_Framework_TestCase
{
    const INDEX = "additionalKey";
    const VALUE = "additionalValue";

    /**
     * @var Core_Model_DomainManager
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

        $this->manager = Core_Model_DiFactory::newDomainManager();
    }

    public function testAddAdditionalFieldShouldReturnTNotFalse()
    {
        $this->assertNotNull($this->manager->addAdditionalField('domainSample1', $this->values));
    }

    public function testAddAdditionalFieldWithValidDataShouldCallSave()
    {
        $domain = $this->getMock('Core_Model_ValueObject_Domain', array('save'), array('domainSample2'));
        $domain->expects($this->once())
               ->method('save')->will($this->returnValue(true));

        Core_Model_DiFactory::registerDomain('domainSample2', $domain);

        $this->manager->addAdditionalField('domainSample2', $this->values);
    }

    public function testAddAdditionalFieldShouldReturnFalseWithNoneExistsParent()
    {
        $this->assertFalse($this->manager->addAdditionalField(null, $this->values));
    }

    public function testAddAdditionalFieldWhitValidDatasetShoulNotCallSave()
    {
        $object = $this->getMock('Core_Model_ValueObject_Domain', array('save'), array('domainSample1'));
        $object->expects($this->never())
               ->method('save');

       $this->manager->addAdditionalField('domainSample1', $this->values);
    }

    public function testAddAdditionalFieldShouldReturnMd5edKey()
    {
        $this->assertEquals(md5('foo'), $this->manager->addAdditionalField('domainSample1', $this->values));
    }

    public function testDeleteAdditionalFieldShouldReturnTrue()
    {
        $this->manager->addAdditionalField('domainSample1', $this->values);

        $this->assertTrue($this->manager->deleteAdditionalField('domainSample1', 'foo'));
    }

    public function testDeleteAdditionalFieldShouldReturnFalseOnNoneExistsField()
    {
        $this->manager->addAdditionalField('domainSample1', $this->values);

        $this->assertTrue($this->manager->deleteAdditionalField('domainSample1', 'foo'));
    }
}