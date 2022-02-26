<?php

declare(strict_types=1);

use TelegramGuardeBot\App;
use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Workers\MemberValidationApprovalTask;


class MemberValidationApprovalTaskTest extends GuardeBotTestCase
{
    /**
     * @testWith [1,2]
     *           [3,4]
     */
    public function testDoRemoveNemMemberFromManager(int $chatId, int $userId) {
        //Arrange
        $sut = new MemberValidationApprovalTask($chatId, $userId);

        App::getInstance()->getDIContainer()->get('newMembersValidationManager')
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($chatId, $userId));

        //Act / Assert
        $sut->do();
    }

    /**
     * @testWith [1,2]
     *           [3,4]
     */
    public function testDoCallBotApproveMember(int $chatId, int $userId) {
        //Arrange
        $sut = new MemberValidationApprovalTask($chatId, $userId);

        App::getInstance()->getDIContainer()->get('bot')
            ->expects($this->once())
            ->method('unmuteMember')
            ->with($this->equalTo($chatId, $userId));

        //Act / Assert
        $sut->do();
    }
}
