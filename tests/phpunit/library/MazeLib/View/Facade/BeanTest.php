<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Facade_BeanTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Facade_BeanTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_View
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
        $customer = new ZendX_Bean(  // ein AbstractBean Erbe der weichere setProperty() hat
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
                'deepStdObject' => (object) array(
                    'hackerz' => 'simple<strong>'
                )
            )
        );
        $test = new MazeLib_View_Facade_Bean($customer, $this->_view);
        
        // escaping
        $this->assertEquals('Foo&lt;ß&gt;', (string) $test->getProperty('name'));
        $this->assertEquals('$', (string) $test->getProperty('//currency'));
        $this->assertEquals('32&lt;Mbit&gt;', (string) $test->getProperty('deepArray/internet'));
        $this->assertEquals('100&lt;Mbit&gt;', (string) $test->getProperty('deepArray/deepArray/internet'));
        $this->assertEquals('Scheiß Encoding', (string) $test->getProperty('deepAbstractBean/ortsteil'));
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
            'deepStdObject' => (object) array(
                'hackerz' => 'simple<strong>'
            )
        );
        $test = new MazeLib_View_Facade_Bean($customer, $this->_view);

        // escaping
        $this->assertEquals('Foo&lt;ß&gt;', (string) $test->getProperty('name'));
        $this->assertEquals('$', (string) $test->getProperty('//currency'));
        $this->assertEquals('32&lt;Mbit&gt;', (string) $test->getProperty('deepArray/internet'));
        $this->assertEquals('100&lt;Mbit&gt;', (string) $test->getProperty('deepArray/deepArray/internet'));
        $this->assertEquals('Scheiß Encoding', (string) $test->getProperty('deepAbstractBean/ortsteil'));
        $this->assertEquals('simple&lt;strong&gt;', (string) $test->getProperty('deepStdObject/hackerz'));
        $this->assertEquals('ViewIteratorX', $test->getProperty('deepStdObject') . 'X');
    }
    
    public function testInitBeanFacadeWithoutData()
    {
        $test = new MazeLib_View_Facade_Bean(NULL, $this->_view);
        
        $this->assertInstanceOf('ZendX_View_Facade_Null', $test->getProperty('name'));
    }
    
}
