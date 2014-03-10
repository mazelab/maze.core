<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Facade_MazeBeanTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Facade_MazeBeanTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ZendX_View_Autoescape
     */
    protected $_view;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->_view = new MazeLib_View_Autoescape();
    }

    protected function setUp()
    {
        $this->_view->clearVars();
    }

    public function testGetProperty()
    {
        $customer = new MazeLib_Bean_TestBean(  
            array(
                'name' => 'Foo<ß>',
                'currency' => '$',
                'deepArray' => array(
                    'internet' => '32<Mbit>',
                    'deepArray' => array(
                        'internet' => '100<Mbit>'
                    )
                ),
                'deepAbstractBean' => new ZendX_Bean(
                    array('ortsteil' => 'Scheiß Encoding')
                ),
                'deepMazeBean' => new MazeLib_Bean(
                    array('ortsteil' => 'Scheiß Encoding')
                ),
                'deepStdObject' => (object) array(
                    'hackerz' => 'simple<strong>'
                )
            )
        );
        $test = new MazeLib_View_Facade_MazeBean($customer, $this->_view);
        
        // escaping
        $this->assertEquals('Foo&lt;ß&gt;', (string) $test->getProperty('name'));
        $this->assertEquals('$', (string) $test->getProperty('//currency'));
        $this->assertEquals('32&lt;Mbit&gt;', (string) $test->getProperty('deepArray/internet'));
        $this->assertEquals('100&lt;Mbit&gt;', (string) $test->getProperty('deepArray/deepArray/internet'));
        $this->assertEquals('Scheiß Encoding', (string) $test->getProperty('deepAbstractBean/ortsteil'));
        $this->assertEquals('Scheiß Encoding', (string) $test->getProperty('deepMazeBean/ortsteil'));
        $this->assertEquals('simple&lt;strong&gt;', (string) $test->getProperty('deepStdObject/hackerz'));
        $this->assertEquals('ViewIteratorX', $test->getProperty('deepStdObject') . 'X');
    }

    public function testInitDataWithArrayShouldBuildIteratorFacade()
    {
        $customer = array(
            'name' => 'Foo<ß>',
            'currency' => '$',
            'deepArray' => array(
                'internet' => '32<Mbit>',
                'deepArray' => array(
                    'internet' => '100<Mbit>'
                )
            ),
            'deepAbstractBean' => new ZendX_Bean(
                    array('ortsteil' => 'Scheiß Encoding')
            ),
            'deepMazeBean' => new MazeLib_Bean(
                    array('ortsteil' => 'Scheiß Encoding')
            ),
            'deepStdObject' => (object) array(
                'hackerz' => 'simple<strong>'
            )
        );
        $test = new MazeLib_View_Facade_MazeBean($customer, $this->_view);

        // escaping
        $this->assertEquals('Foo&lt;ß&gt;', (string) $test->getProperty('name'));
        $this->assertEquals('$', (string) $test->getProperty('//currency'));
        $this->assertEquals('32&lt;Mbit&gt;', (string) $test->getProperty('deepArray/internet'));
        $this->assertEquals('100&lt;Mbit&gt;', (string) $test->getProperty('deepArray/deepArray/internet'));
        $this->assertEquals('Scheiß Encoding', (string) $test->getProperty('deepAbstractBean/ortsteil'));
        $this->assertEquals('Scheiß Encoding', (string) $test->getProperty('deepMazeBean/ortsteil'));
        $this->assertEquals('simple&lt;strong&gt;', (string) $test->getProperty('deepStdObject/hackerz'));
        $this->assertEquals('ViewIteratorX', $test->getProperty('deepStdObject') . 'X');
    }
    
    public function testInitBeanFacadeWithoutData()
    {
        $test = new MazeLib_View_Facade_MazeBean(NULL, $this->_view);
        
        $this->assertInstanceOf('ZendX_View_Facade_Null', $test->getProperty('name'));
    }
    
    public function testGetConflictsOnAddedPropertyShouldReturnAllConflictedProperties()
    {
        $val = array(
            'array/key' => array(
                'local' => '11.11.11.11',
                'status' => 2,
                'remote' => '12.12.12.12'
            )
        );
        
        $data = new MazeLib_Bean_TestBean($val);
        $test = new MazeLib_View_Facade_MazeBean($data, $this->_view);
        
        $this->assertEquals($val, $test->getConflicts());
    }
    
    public function testGetConflictsOnAddedLocalAndRemotePropertyShouldReturnAllConflictedProperties()
    {
        $result = array(
            'array/key' => array(
                'local' => '11.11.11.30',
                'status' => 1,
                'remote' => '11.11.11.40'
            )
        );
        
        $data = new MazeLib_Bean_TestBean();
        $data->setLocalProperty('array/key', '11.11.11.30');
        $data->setRemoteProperty('array/key', '11.11.11.40');
        
        $test = new MazeLib_View_Facade_MazeBean($data, $this->_view);
        
        $this->assertEquals($result, $test->getConflicts());
    }
    
    public function testGetConflictsOnEmptyPropertyShouldReturnEmptyArray()
    {
        $test = new MazeLib_View_Facade_MazeBean(array(), $this->_view);
        
        $this->assertEquals(array(), $test->getConflicts());
    }
    
    public function testHasConflictShouldReturnTrue()
    {
        $data = new MazeLib_Bean_TestBean(  
            array(
                'array/key' => array(
                    'local' => '11.11.11.11',
                    'status' => 2,
                    'remote' => '12.12.12.12'
                )
            )
        );
        $test = new MazeLib_View_Facade_MazeBean($data, $this->_view);
        
        $this->assertTrue($test->hasConflict('array/key'));
    }
    
    public function testHasConflictShouldReturnFalse()
    {
        $data = new MazeLib_Bean_TestBean(  
            array(
                'ip' => array(
                    'local' => '11.11.11.11',
                    'status' => 2,
                    'remote' => '11.11.11.11'
                )
            )
        );
        $test = new MazeLib_View_Facade_MazeBean($data, $this->_view);
        
        $this->assertFalse($test->hasConflict('ip'));
    }
    
    public function testHasConflictOnEmptyPropertyShouldReturnFalse()
    {
        $test = new MazeLib_View_Facade_MazeBean(array(), $this->_view);
        
        $this->assertFalse($test->hasConflict('ip'));
    }
    
    public function testIsConflictedShouldReturnTrue()
    {
        $data = new MazeLib_Bean_TestBean(  
            array(
                'array/key' => array(
                    'local' => '11.11.11.11',
                    'status' => 2,
                    'remote' => '12.12.12.12'
                )
            )
        );
        $test = new MazeLib_View_Facade_MazeBean($data, $this->_view);
        
        $this->assertTrue($test->isConflicted());
    }
    
    public function testIsConflictedShouldReturnFalse()
    {
        $data = new MazeLib_Bean_TestBean(  
            array(
                'ip' => array(
                    'local' => '11.11.11.11',
                    'status' => 2000,
                    'remote' => '11.11.11.11'
                )
            )
        );
        $test = new MazeLib_View_Facade_MazeBean($data, $this->_view);
        
        $this->assertFalse($test->isConflicted());
    }
    
    public function testIsConflictedOnEmptyPropertyShouldReturnFalse()
    {
        $test = new MazeLib_View_Facade_MazeBean(array(), $this->_view);
        
        $this->assertFalse($test->isConflicted());
    }
    
}
