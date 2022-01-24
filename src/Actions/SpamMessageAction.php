<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Actions;

use TelegramGuardeBot\Learners\MlSpamTextLearner;

/**
 * A message action that classify a message as spam
 */
class SpamMessageAction extends MessageAction
{
    private $spamLearner;

    public function __construct()
    {
        $this->spamLearner = new MlSpamTextLearner();
    }

    public function act($messageText) : void
    {
        $this->spamLearner->learn($messageText);
    }
}
