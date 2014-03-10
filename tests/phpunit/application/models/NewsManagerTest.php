<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_NewsManagerTest
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_NewsManagerTest extends PHPUnit_Framework_TestCase
{
    protected $_newsManager = null;

    public function setUp()
    {
        Core_Model_Dataprovider_DiFactory::setAdapter("Demo");
        
        $this->_newsManager = Core_Model_DiFactory::getNewsManager();
    }

    public function testGetMessageShouldReturnAnArray()
    {
        $this->assertInternalType("array", $this->_newsManager->getMessage(4456780));
    }

    public function testGetMessageWithStringAsFilterShouldReturnsTheMessageWithSameFilterAsStatus()
    {
        $filter  = "draft";
        $message = $this->_newsManager->getMessage(4456784, $filter);
        $this->assertEquals($message["status"], $filter);
    }

    public function testGetMessageWithStringAsFilterShouldReturnsAnNotEmptyArray()
    {
        $filter  = "draft";
        $message = $this->_newsManager->getMessage(4456784, $filter);

        $this->assertInternalType("array", $message);
        $this->assertNotEmpty($message);
    }

    public function testGetMessageWithStringAsFilterOnNoneExistsStatusShouldReturnsNull()
    {
        $this->assertNull($this->_newsManager->getMessage(4456784, "noneStatus"));
    }

    public function testGetMessageWithEmptyArrayAsFilterShouldReturnsAnNotEmptyArray()
    {
        $message = $this->_newsManager->getMessage(4456784, array());

        $this->assertInternalType("array", $message);
        $this->assertNotEmpty($message);
    }

    public function testGetMessageWithArrayAsFilterShouldReturnsAnNotEmptyArray()
    {
        $filter  = array("draft");
        $message = $this->_newsManager->getMessage(4456784, $filter);

        $this->assertInternalType("array", $message);
        $this->assertNotEmpty($message);
    }

    public function testGetMessageWithArrayAsFilterShouldReturnsAnArrayWithSameFilterAsStatus()
    {
        $filter  = array("draft");
        $message = $this->_newsManager->getMessage(4456784, $filter);

        $this->assertArrayHasKey("status", $message);
        $this->assertEquals($message["status"], $filter[0]);
    }

    public function testGetMessageWithArrayAsFilterShouldReturnsNullBecauseStatusDoesNotMatch()
    {
        $this->assertNull($this->_newsManager->getMessage(4456784, array("noneStatus")));
    }

    public function testGetMessageWithMultipleFiltersAsArrayShouldReturnsOneNotEmptyArrayBecauseTheSecondMatched()
    {
        $filter  = array("noneStatus", "public");
        $message = $this->_newsManager->getMessage(4456783, $filter);

        $this->assertInternalType("array", $message);
        $this->assertNotEmpty($message);
    }

    public function testGetMessageWithMultipleFiltersAsArrayShouldReturnsOneArrayWithSameFilterAsStatus()
    {
        $filter  = array("noneStatus", "public");
        $message = $this->_newsManager->getMessage(4456783, $filter);

        $this->assertArrayHasKey("status", $message);
        $this->assertEquals($message["status"], $filter[1]);
    }

    public function testGetMessageWhichDoesNotExistsShouldReturnsNull()
    {
        $this->assertNull($this->_newsManager->getMessage(2));
    }

    public function testDeleteMessageShouldReturnTrueAndTheGivenMessageGetterShouldReturnsNull()
    {
        $this->assertTrue($this->_newsManager->deleteMessage(4456781));
        $this->assertNull($this->_newsManager->getMessage(4456781));
    }

    public function testDeleteMessageWithNullAsIdShouldReturnFalse()
    {
        $this->assertFalse($this->_newsManager->deleteMessage(false));
    }

    public function testDeleteMessageOnNoneExistsMessageShouldReturnFalse()
    {
        $this->assertFalse($this->_newsManager->deleteMessage(2));
    }

    public function testUpdateMessageShouldBeIgnoreTheIdKeyInDataset()
    {
        $update = array(
            "_id"   => "newID",
            "title" => "changedTitle"
        );

        $this->_newsManager->updateMessage(4456780, $update);
        $this->assertNull($this->_newsManager->getMessage($update["_id"]));
        $this->assertNotEmpty($this->_newsManager->getMessage(4456780));
    }

    public function testUpdateMessageTitleShouldReturnTrue()
    {
        $update = array("title" => "changedTitle");

        $this->assertTrue($this->_newsManager->updateMessage(4456780, $update));
        $this->assertNotNull($this->_newsManager->getMessageByTitle($update["title"]));
    }

    public function testUpdateMessageTitleShouldReturnAnNotEmptyArrayByTheNextGetMessageByTitle()
    {
        $update = array("title" => "changedTitle");
        $this->_newsManager->updateMessage(4456780, $update);
        $message = $this->_newsManager->getMessageByTitle($update["title"]);

        $this->assertInternalType("array", $message);
        $this->assertNotNull($message);
    }

    public function testUpdateNoneExitsMessageShouldReturnFalse()
    {
        $this->assertFalse($this->_newsManager->updateMessage(2, array("title" => "changedTitle")));
    }

    public function testUpdateMessageWithAnEmptyArrayShouldReturnFalse()
    {
        $this->assertFalse($this->_newsManager->updateMessage(4456780, array()));
    }

    public function testUnsetMessageTitleDatasetShouldReturnTrue()
    {
        $this->assertTrue($this->_newsManager->updateMessage(4456780, array("title" => null)));
        $message = $this->_newsManager->getMessage(4456780);
        $this->assertFalse(isset($message["title"]));
    }

    public function testUnsetMessageIdShouldNotWorkAndReturnArray()
    {
        $this->_newsManager->updateMessage(4456780, array("_id" => null));
        $this->assertInternalType("array", $this->_newsManager->getMessage(4456780));
    }

    public function testGetMessagesByTagsAsStringShouldReturnsArrayWithTwoMatches()
    {
        $message = $this->_newsManager->getMessagesByTags("two");

        $this->assertInternalType("array", $message);
        $this->assertCount(2, $message);
    }

    public function testGetMessagesByTagsAsStringShouldReturnOnMatchedArrayWithTheSameFilterAsStatus()
    {
        $message = $this->_newsManager->getMessagesByTags("once");

        $this->assertInternalType("array", $message);
        $this->assertCount(1, $message);
    }

    public function testGetMessagesByTagsShouldReturnAnArrayWithTwoMatches()
    {
        $message = $this->_newsManager->getMessagesByTags(array("two"));

        $this->assertInternalType("array", $message);
        $this->assertCount(2, $message);
    }

    public function testGetMessagesByTagsWithMultipleTagsShouldReturnNotEmptyArrayWithThreeMatches()
    {
        $tags = array("two", "once");
        $message = $this->_newsManager->getMessagesByTags($tags);

        $this->assertInternalType("array", $message);
        $this->assertCount(3, $message);
    }

    public function testGetMessagesByTagsWithMultipleTagsAndLimitShouldReturnTwoMatchedArrays()
    {
        $tags = array("two", "once");
        $this->assertCount(2, $this->_newsManager->getMessagesByTags($tags, 2));
    }

    public function testGetMessagesByTagsWithMultipleTagsAndZeroLimitShouldReturnAnEmptyArray()
    {
        $tags = array("two", "once");
        $this->assertEmpty($this->_newsManager->getMessagesByTags($tags, 0));
    }

    public function testGetMessagesByTagsWithEmptyTagsShouldReturnAnEmptyArray()
    {
        $this->assertEmpty($this->_newsManager->getMessagesByTags(array()));
    }

    public function testCreateMessageWithContextIdShouldIgnoresThisContext()
    {
        $data = array(
            "_id"           => "4456781",
            "title"         => "createdMessage",
            "content"       => "...",
            "teaser"        => "...",
            "status"        => "public",
            "tags"          => array(
                "13bde19ebbf62413cb61ca5b71d1f39c" => "new one",
                "081d29b9330707cc21a1bf4132f7d3f7" => "intern"
            )
        );

        $messageId = $this->_newsManager->createMessage($data);
        $data["_id"] = $messageId;

        $this->assertNotNull($messageId);
        $this->assertEquals($this->_newsManager->getMessage($messageId), $data);
    }

    public function testGetAllMessagesShouldReturnManyArrays()
    {
        $this->assertGreaterThan(4, sizeof($this->_newsManager->getMessages()));
    }

    public function testGetAllMessagesWithLimitShouldReturnLimitedArray()
    {
        $messages = $this->_newsManager->getMessages(2);

        $this->assertInternalType("array", $messages);
        $this->assertCount(2, $messages);
    }

    public function testGetMessagesWithFilteredStatusMessageShouldReturnOneNotEmptyArray()
    {
        $filter  = array("draft");
        $message = $this->_newsManager->getMessages(null, $filter);

        $this->assertInternalType("array", $message);
        $this->assertCount(1, $message);
    }

    public function testGetMessagesWithTwoFilteredStatusMessageShouldReturnArrayWithFiveEntries()
    {
        $filter  = array("public", "draft");
        $this->assertCount(5, $this->_newsManager->getMessages(null, $filter));
    }

    public function testGetMessagesWithNoneExistsFilterShouldReturnAnEmptyArray()
    {
        $this->assertEmpty($this->_newsManager->getMessages(4456784, array("noneStatus")));
    }

    public function testAddMessageTagWithArrayShouldReturnArray()
    {
        $this->_newsManager->addTags(4456784, array("tag1"));
        $message = $this->_newsManager->getMessage(4456784);

        $this->assertInternalType("array", $message["tags"]);
    }

    public function testAddMessageTagsShouldReturnAnNotEmptyArrayWithTwoEntries()
    {
        $this->_newsManager->addTags(4456784, array("tag1", "tag2"));
        $message = $this->_newsManager->getMessage(4456784);

        $this->assertCount(2, $message["tags"]);
    }

    public function testAddMessageTagWithEmptyArrayShouldNotBeSettedAndRetursnFalse()
    {
        $this->assertFalse($this->_newsManager->addTags(4456784, array()));
    }

    public function testAddMessageTagWillUnsetOneTag()
    {
        $this->_newsManager->addTags(4456784, array("tagOne","tagTwo"));
        $this->_newsManager->addTags(4456784, array(
            "266926bae82b01bd25f364ae7adb63df" => null
        ));

        $message = $this->_newsManager->getMessage(4456784);
        $this->assertCount(1, $message["tags"]);
    }

}