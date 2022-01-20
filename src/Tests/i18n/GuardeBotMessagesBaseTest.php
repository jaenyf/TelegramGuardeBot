<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\i18n\GuardeBotMessagesBase;

require fromAppSource("i18n/GuardeBotMessages_EN.php");

class GuardeBotMessagesBaseTest extends GuardeBotTestCase
{
    public function testGetReturnExpected()
    {
        //Act
        $tested = GuardeBotMessagesBase::get(GuardeBotMessagesBase::FCMD_BAN_MESSAGE_AUTHOR);

        //Assert
        $this->assertThat($tested, $this->equalTo("Ban this author"));
    }

    public function testGetLoweredReturnExpected()
    {
        //Act
        $tested = GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::FCMD_BAN_MESSAGE_AUTHOR);

        //Assert
        $this->assertThat($tested, $this->equalTo("ban this author"));
    }
}
