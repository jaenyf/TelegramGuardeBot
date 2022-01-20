<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Actions\LogToFileMessageAction;

class LogToFileMessageActionTest extends GuardeBotTestCase
{

    private const testFileName = ".LogToFileMessageActionTest.test";

    public function setUp() : void
    {
        $this->cleanUp();
    }

    public function tearDown() : void
    {
        $this->cleanUp();
    }

    public function cleanUp() : void
    {
        if(file_exists(self::testFileName))
        {
            unlink(self::testFileName);
        }
    }

    public function testConstructThrowsForEmptyFileName()
    {
        $this->expectException("InvalidArgumentException");
        new LogToFileMessageAction("");
    }

    public function testActWriteToFile()
    {
        //Arrange
        $sut = new LogToFileMessageAction(self::testFileName);

        //Act
        $sut->act("this is a test message");

        //Assert
        $tested = file_get_contents(self::testFileName);
        $this->assertThat($tested, $this->equalTo(json_encode("this is a test message").PHP_EOL));
    }

}
