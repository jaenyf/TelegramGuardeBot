<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests\Managers;

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Managers\CsvMembersManager;
use TelegramGuardeBot\Managers\Spams\SpammersManager;
use TelegramGuardeBot\Managers\Masters\MastersManager;

abstract class CsvMembersManagerTest extends GuardeBotTestCase
{
    protected abstract function getSut() : CsvMembersManager;
    protected abstract function getFileNamesToClean() : array;
    protected abstract function getTestFileName() : string;

    public function setUp() : void
    {
        foreach($this->getFileNamesToClean() as $fileName)
        {
            if(!file_exists($fileName . '.bak') && file_exists($fileName))
            {
                rename($fileName, $fileName . '.bak');
            }
        }
    }

    public function tearDown() : void
    {
        $this->cleanUp();

        foreach($this->getFileNamesToClean() as $fileName)
        {
            if(file_exists($fileName . '.bak'))
            {
                rename($fileName . '.bak', $fileName);
            }
        }
    }

    private function cleanUp()
    {
        foreach($this->getFileNamesToClean() as $fileName)
        {
            if(file_exists($fileName))
            {
                unlink($fileName);
            }
        }
    }

    public function testAddWriteToFile()
    {
        //Arrange
        $sut = $this->getSut();

        //Act
        $sut->add(42, "johndoe", "john", "doe");

        //Assert
        $tested = file_get_contents($this->getTestFileName());
        $this->assertThat($tested, $this->equalTo("ID,USERNAME,FIRST_NAME,LAST_NAME\n42,johndoe,john,doe\n"));
    }


    public function testHasReadFromFile()
    {
        //Arrange
        $sut = $this->getSut();
        $sut->add(26, "johndoe", "john", "doe");

        //Act
        $tested = $sut->has(26);

        //Assert
        $this->assertThat($tested, $this->equalTo(true));
    }

    public function testCsvMembersManagerGetInstanceDontMixesOverTypes()
    {
        //Arrange / Act
        $spammerManager = SpammersManager::getInstance();
        $masterManager = MastersManager::getInstance();

        //Assert
        $this->assertThat($spammerManager, $this->logicalNot($this->equalTo($masterManager)));
    }

}
