<?php

namespace TelegramGuardeBot\Helpers;

/**
 * MemoryHelper Class.
 *
 * @author jaenyf
 */
class MemoryHelper
{
    private static string $storedMemoryLimit;

    /**
     * Store the current memory_limit and set a new value
     * @return The current memory_limit value
     * @throws \ErrorException on failure
     */
    public static function storeAndSetMemoryLimit(string $newMemoryLimit) : string
    {
        $limit =  ini_set('memory_limit', $newMemoryLimit);
        if($limit === false)
        {
            throw new \ErrorException("Failed to retrieve memory limit");
        }

        return (self::$storedMemoryLimit = $limit);
    }

    /**
     * Restore the previous memory_limit stored with MemoryHelper::storeAndSetMemoryLimit
     * @throws \ErrorException on failure
     */
    public static function restoreMemoryLimit() : void
    {
        if(!isset(self::$storedMemoryLimit))
        {
            throw new \ErrorException("Memory limit has not been previously stored");
        }

        if(false === ini_set("memory_limit", self::$storedMemoryLimit))
        {
            throw new \ErrorException("Failed to restore memory limit");
        }
    }
}
