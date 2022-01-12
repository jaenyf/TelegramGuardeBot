<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Helpers\MarkdownHelper;

final class MarkdownHelperTest extends GuardeBotTestCase
{
    public function testEscapeAddBackslashes(): void
    {
        //Act
        $tested = MarkdownHelper::escape("_*][)(~`>#+-=|}{.!");

        //Assert
        $this->assertThat($tested, $this->equalTo('\_\*\]\[\)\(\~\`\>\#\+\-\=\|\}\{\.\!'));
    }
}
