<?php
declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Actions\MessageActionProcessor;
use TelegramGuardeBot\Helpers\ArrayHelper;

class MessageActionProcessorTest extends GuardeBotTestCase
{

    private $testMessageTextUpdate;

    private const logFileTestName = ".logFileTestName.test";

    public function cleanUp() : void
    {
        if(file_exists(self::logFileTestName))
        {
            unlink(self::logFileTestName);
        }
    }

    public function tearDown() : void
    {
        $this->cleanUp();
    }

    public function setUp() : void
    {
        $this->cleanUp();

        $this->testMessageTextUpdate = ArrayHelper::toObject([
                "update_id" => 123456789,
                "message" => [
                    "message_id" => 123,
                    "from" => [
                        "id" => 1234567890,
                        "is_bot" => false,
                        "first_name" => "test",
                        "language_code" => "en"
                    ],
                    "chat"=>[
                        "id" => -1234567890123,
                        "title" => "test chat group",
                        "type" => "supergroup"
                    ],
                    "date" => 1234567890,
                    "text" => "This is a test"
                ]
        ]);
    }

    public function testProcessHandleLogToFile()
    {
        //Arrange
        $messageAction = ArrayHelper::toObject([
            "chatId" => -1234567890123,
            "onType" => "message",
            "actions" => ["logToFile", "spam"],
            "fileName" => self::logFileTestName
        ]);
        $sut = new MessageActionProcessor();

        //Act
        $sut->process([$messageAction], $this->testMessageTextUpdate);

        //Assert
        $tested = file_get_contents(self::logFileTestName);
        $this->assertThat($tested, $this->equalTo(json_encode("This is a test").PHP_EOL));
    }
}
