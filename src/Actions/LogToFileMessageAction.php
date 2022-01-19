<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Actions;

class LogToFileMessageAction extends MessageAction
{

    private $fileName;

    public function __construct(string $fileName)
    {
        if(empty($fileName))
        {
            throw new \InvalidArgumentException("fileName is not defined");
        }
        $this->fileName = $fileName;
    }

    public function act($messageText) : void
    {
        file_put_contents($this->fileName, json_encode($messageText).PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
