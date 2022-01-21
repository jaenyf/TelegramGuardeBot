<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\Workers\BackgroundProcessWorker;

interface BackgroundProcessInstanciator
{
    public function getBackgroundProcess(string $uid): BackgroundProcessWorker;
}
