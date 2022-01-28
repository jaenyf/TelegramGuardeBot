<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Actions;
use TelegramGuardeBot\App;
use TelegramGuardeBot\Learners\MlSpamTextLearner;

/**
 * A message action that classify a message as spam
 */
class SpamMessageAction extends MessageAction
{
    private $spamLearner;

    public function __construct()
    {
        $this->spamLearner = App::getInstance()->getDIContainer()->get(MlSpamTextLearner::class);
    }

    public function act($messageText) : void
    {
        $this->spamLearner->learn($messageText);
    }
}
