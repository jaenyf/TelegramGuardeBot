<?php

namespace TelegramGuardeBot\Helpers;

class FileHelper
{
    public static function lock(string $fileName)
    {
        $lockFilePointer = fopen($fileName, "w");

        if (false === $lockFilePointer) {
            self::ThrowLockFailed();
        }

        if (!flock($lockFilePointer, LOCK_EX)) {
            self::ThrowLockFailed();
        }

        return $lockFilePointer;
    }

    public static function unlock($lockFilePointer, $fileName)
    {
        if (!isset($lockFilePointer)) {
            throw new \ErrorException('Not locked');
        }

        $result = true;
        $result &= flock($lockFilePointer, LOCK_UN);
        $result &= fclose($lockFilePointer);
        $result &= unlink($fileName);

        if (!$result) {
            self::ThrowUnlockFailed();
        }

        return null;
    }

    private static function ThrowLockFailed()
    {
        throw new \ErrorException('Failed to obtain lock');
    }

    private static function ThrowUnlockFailed()
    {
        throw new \ErrorException('Failed to release lock');
    }
}
