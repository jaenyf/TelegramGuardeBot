<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\App;

final class AppTest extends GuardeBotTestCase
{
    /**
     * @group noSetUp
     */
    public function testInitializeDoesNotThrow(): void
    {
        //Act / Assert
        $this->assertThat(App::initialize(self::appConfigFileName), $this->isNull());
    }

    public function testGetInstanceReturnsAnInstance(): void
    {
        //Act
        $sut = App::getInstance();

        //Assert
        $this->assertThat($sut, $this->logicalNot($this->isNull()));
    }

    public function testGetDIContainerReturnsAnInstance(): void
    {
        //Act
        $tested = App::getInstance()->getDIContainer();

        //Assert
        $this->assertThat($tested, $this->logicalNot($this->isNull()));
    }

    public function testGetBotReturnsAnInstance(): void
    {
        //Act
        $sut = App::getInstance();

        //Assert
        $this->assertThat($sut->getBot(), $this->logicalNot($this->isNull()));
    }
}
