<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Helpers\MemoryHelper;

final class MemoryHelperTest extends GuardeBotTestCase
{

    /**
     * This function just ensure we set another memory limit otherwise our tests would be broken
     */
    private function alterMemoryLimit(string $memoryLimit) : string
    {
        if($memoryLimit == "-1")
        {
            return "512M";
        }

        preg_match_all('/(\d+)\w*/', $memoryLimit, $matches);
        $value = (int)$matches[1][0];
        ++$value;

        return preg_replace('/(\d+)(\w*)/', ''.$value.'$2', $memoryLimit);
    }

    /**
     * @testWith    ["-1", "512M"]
     *              ["512M", "513M"]
     *              ["1G", "2G"]
     *              ["3G", "4G"]
     */
    public function testThisAlterMemoryLimit(string $actual, string $expected)
    {
        //Act
        $tested = $this->alterMemoryLimit($actual);

        //Assert
        $this->assertThat($tested, $this->equalTo($expected));
    }

    public function testStoreAndSetMemoryLimitReturnsCurrentMemoryLimit(): void
    {
        //Arrange
        $currentMemoryLimit = ini_get("memory_limit");

        //Act
        $tested = MemoryHelper::storeAndSetMemoryLimit($this->alterMemoryLimit($currentMemoryLimit));

        //Assert
        $this->assertThat($tested, $this->equalTo($currentMemoryLimit));
    }

    public function testStoreAndSetMemoryLimitChangeCurrentMemoryLimit(): void
    {
        //Arrange
        $previousMemoryLimit = ini_get("memory_limit");

        //Act
        MemoryHelper::storeAndSetMemoryLimit($this->alterMemoryLimit($previousMemoryLimit));
        $currentMemoryLimit = ini_get("memory_limit");

        //Assert
        $this->assertThat($previousMemoryLimit, $this->logicalNot($this->equalTo($currentMemoryLimit)));
    }

    public function testRestoreMemoryLimit(): void
    {
        //Arrange
        $previousMemoryLimit = ini_get("memory_limit");

        //Act
        MemoryHelper::storeAndSetMemoryLimit($this->alterMemoryLimit($previousMemoryLimit));
        MemoryHelper::restoreMemoryLimit();
        $currentMemoryLimit = ini_get("memory_limit");

        //Assert
        $this->assertThat($previousMemoryLimit, $this->equalTo($currentMemoryLimit));
    }
}
