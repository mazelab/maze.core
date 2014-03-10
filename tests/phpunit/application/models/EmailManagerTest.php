<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_EmailManagerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_EmailManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Core_Model_EmailManager
     */
    private $manager = null;

    public function setUp()
    {
        Core_Model_DiFactory::reset();
        Core_Model_Dataprovider_DiFactory::setAdapter("Demo");

        $cryptManager = $this->getMock("Core_Model_CryptManager", array("getSecureKey"));
        $cryptManager->expects($this->any())->method("getSecureKey")->will($this->returnValue("0x00ServerHashFromFile"));
        Core_Model_DiFactory::setCryptManager($cryptManager);

        $transport = $this->getMock("Zend_Mail_Transport_Sendmail", array("send"));
        $transport->expects($this->any())->method("send")->will($this->returnValue(true));

        $this->manager = new Core_Model_EmailManager;
        $this->manager->setTransport($transport);
    }

    public function testGetSmtpOptionsWithNonExistingOptionShouldReturnNull()
    {
        $this->manager = new Core_Model_EmailManager;
        $this->assertNull($this->manager->getSmtpOptions("nonExists"));
    }

    public function testSetCharsetShouldSetCharset()
    {
        $this->manager->setCharset("iso-8859-1");

        $this->assertEquals($this->manager->getCharset(), "iso-8859-1");
    }

    public function testGetFromWithNoSettedValueShouldReturnAnEmptyArray()
    {
        $this->assertInternalType("array", $this->manager->getFrom());
        $this->assertEmpty($this->manager->getFrom());
    }
    
    public function testSetFromShouldReturnArrayThatContainsTheEmailKey()
    {
        $this->manager->setFrom("one@example.com");

        $this->assertInternalType("array", $this->manager->getFrom());
        $this->assertArrayHasKey("email", $this->manager->getFrom());
    }

    public function testSetFromShouldReturnArrayThatContainsTheSameEmail()
    {
        $this->manager->setFrom("one@example.com");

        $this->assertEquals("one@example.com", $this->manager->getFrom("email"));
    }

    public function testSetFromWithArrayShouldReturnArrayThatContainsTheEmailKey()
    {
        $this->manager->setFrom(array("one@example.com"));

        $this->assertArrayHasKey("email", $this->manager->getFrom());
    }

    public function testSetFromWithArrayShouldReturnArrayThatContainsTheSameEmail()
    {
        $this->manager->setFrom(array("one@example.com"));

        $this->assertEquals("one@example.com", $this->manager->getFrom("email"));
    }

    public function testSetOptionWithSubjectShouldSetSubject()
    {
        $this->manager->setOptions(array(
            "subject" => "test"
        ));

        $this->assertEquals("test", $this->manager->getSubject());
    }

    public function testSetOptionWithSetterPrefixShouldBeIgnored()
    {
        $this->manager->setOptions(array(
            "setSubject" => "test"
        ));

        $this->assertNull($this->manager->getSubject());
    }

    public function testSetToAfterAddToShouldOverwriteAddedOnes()
    {
        $new = array("two@example.com", "three@example.com");
        
        $this->manager->addTo("one@example.com")
                      ->setTo($new);

        $this->assertEquals($this->manager->getTo(), $new);
    }

    public function testSetToShouldReturnAnArrayWithTwoElements()
    {
        $this->manager->setTo(array("one@example.com", "two@example.com"));

        $this->assertCount(2, $this->manager->getTo());
    }

    public function testAddToShouldReturnAnArrayWithTwoElements()
    {
        $this->manager->addTo("two@example.com")
                      ->addTo("one@example.com");

        $this->assertCount(2, $this->manager->getTo());
    }

    public function testSetBodyWithHtmlFlagShouldSetContentTypeHtml()
    {
        $this->manager->setBody("...", true);

        $this->assertEquals($this->manager->getContentType(), "html");
    }
    
    public function testGetTransportShouldReturnDefaultTransportObject()
    {
        $this->assertInstanceOf('Zend_Mail_Transport_Sendmail', $this->manager->getTransport());
    }
    
    public function testSetTransportShouldSetTransportObject()
    {
        $mock = $this->getMock('Zend_Mail_Transport_Sendmail');
        
        $this->manager->setTransport($mock);
        
        $this->assertEquals($mock, $this->manager->getTransport());
    }
    
    public function testGetTransportWithSmtpConfigurationShouldReturnSmtpTransportObject()
    {
        $this->manager->setBody("...")
                      ->addTo("one@example.com")
                      ->setSmtpOptions(array(
            "host"     => "example.com",
            "auth"     => "plain",
            "username" => "smtp@example.com",
            "password" => "secret",
            "port"     => "25",
            "ssl"      => "tls"
        ));
        $this->manager->send();

        $this->assertInstanceOf('Zend_Mail_Transport_Smtp', $this->manager->getTransport());
    }

    public function testSendShouldReturnTrue()
    {
        $this->manager->addTo('one@example.com')
                      ->setBody("...")
                      ->addTo("one@example.com");
        
        $this->assertTrue($this->manager->send());
    }
    
    public function testSendWhenSuccessfulShouldNotSetException()
    {
        $this->manager->addTo('one@example.com')
                      ->setBody("...")
                      ->addTo("one@example.com")
                      ->send();
        
        $this->assertFalse($this->manager->hasException());
    }
    
    public function testSendWithoutTargetShouldReturnFalse()
    {
        $this->manager = new Core_Model_EmailManager;
        $this->manager->setBody("...");

        $this->assertFalse($this->manager->send());
    }    
    
    public function testSendWhenFailedShouldSetException()
    {
        $this->manager = new Core_Model_EmailManager;
        $this->manager->setBody("...")->send();

        $this->assertTrue($this->manager->hasException());
    }
    
    public function testSendWithoutSubjectShouldReturnTrue()
    {
        $this->manager->setBody("...")
                      ->addTo("one@example.com");

        $this->assertTrue($this->manager->send());
    }

    public function testSendWithoutSmtpHostShouldReturnFalse()
    {
        $this->manager->setBody("...")
                      ->addTo("one@example.com");
        
        $this->manager->setSmtpOptions(array(
            "auth"     => "plain",
            "username" => "smtp@example.com",
            "password" => "secret",
            "port"     => "25",
            "ssl"      => "tls"
        ));

        $this->assertTrue($this->manager->send());
        $this->assertInstanceOf("Zend_Mail_Transport_Sendmail", $this->manager->getTransport());
    }

    public function testSendShouldSetSmtpConfigFromMazeConfig()
    {
        $config = Core_Model_DiFactory::getConfig();
        $config->setData(array(
            "mail" => array(
                "from" =>  array (
                    "email"    => "one@example.com",
                    "name"     => "Mr. Example.com",
                ),
                "smtp" =>  array (
                    "auth"     => "plain",
                    "host"     => "smtp.example.com",
                    "username" => "smtp@example.com",
                    "password" => "secret",
                    "port"     => "25",
                    "ssl"      => "tls"
                ),
                "smtpEnabled"  => true,
            )
        ));

        /**
         * @todo config should be set in a simple matter like descriped in setSmtpOptions mehtod anotations
         */
        $smtpMap = array("config" => $config->getData("mail/smtp"));
        $smtpMap["host"] = $smtpMap["config"]["host"];
        unset($smtpMap["config"]["host"]);

        $this->manager = new Core_Model_EmailManager;
        $this->manager->send();

        $this->assertEquals($this->manager->getSmtpOptions(), $smtpMap);
    }

    public function testSendShouldNotSetSmtpConfigFromMazeConfigWhileSmtpIsDisabled()
    {
        $config = Core_Model_DiFactory::getConfig();
        $config->setData(array(
            "mail" => array(
                "smtp" =>  array (
                    "host"     => "smtp.example.com",
                    "username" => "smtp@example.com"
                ),
                "smtpEnabled"  => false
            )
        ));

        $this->manager = new Core_Model_EmailManager;
        $this->manager->send();
        $this->assertInternalType("array", $this->manager->getSmtpOptions());
        $this->assertEmpty($this->manager->getSmtpOptions());
    }

    public function testSendShouldSetFromPropertyFromMazeConfig()
    {
        $config = Core_Model_DiFactory::getConfig();
        $config->setData(array(
            "mail" => array(
                "from" =>  array (
                    "email" => "one@example.com",
                    "name"  => "Mr. Example.com"
                )
            )
        ));

        $this->manager = new Core_Model_EmailManager;
        $this->manager->send();
        $this->assertInternalType("array", $this->manager->getFrom());
        $this->assertEquals($this->manager->getFrom(), $config->getData("mail/from"));
    }

    public function testSendShouldNotSetFromPropertyFromMazeConfigWithOnlyNameProperty()
    {
        $config = Core_Model_DiFactory::getConfig();
        $config->setData(array(
            "mail" => array(
                "from" =>  array (
                    "name" => "Mr. Example.com"
                )
            )
        ));

        $this->manager = new Core_Model_EmailManager;
        $this->manager->send();
        $this->assertEmpty($this->manager->getFrom());
    }

    public function testSendShouldSetFromPropertyFromMazeConfigWithOnlyEmailProperty()
    {
        $config = Core_Model_DiFactory::getConfig();
        $config->setData(array(
            "mail" => array(
                "from" =>  array (
                    "email" => "one@example.com"
                )
            )
        ));

        $this->manager = new Core_Model_EmailManager;
        $this->manager->send();
        $this->assertNull($this->manager->getFrom("name"));
        $this->assertEquals($config->getData("mail/from/email"), $this->manager->getFrom("email"));
    }

    public function testSetToAdminsShouldReturnArrayWithTwoEntries()
    {
        $this->manager->setToAdmins();

        $this->assertInternalType("array", $this->manager->getTo());
        $this->assertCount(2, $this->manager->getTo());
    }

    public function testSetToClientsShouldReturnArrayWithTwoEntries()
    {
        $this->manager->setToClients();

        $this->assertInternalType("array", $this->manager->getTo());
        $this->assertCount(2, $this->manager->getTo());
    }

    public function testSetSubjectShouldTranslateSubject()
    {
        $frContent = array("good_morning" => "Bon jour");
        $translate = new Zend_Translate(array(
            "adapter" => "array",
            "content" => $frContent,
            "locale"  => "fr"
        ));
        Zend_Registry::set("Zend_Translate", $translate);

        $manager = new Core_Model_EmailManager;
        $manager->setSubject("good_morning");

        $this->assertEquals($manager->getSubject(), $frContent["good_morning"]);
        $this->assertEquals($manager->getSubject(), $translate->translate("good_morning"));
    }
    
}
