<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers;

use TelegramGuardeBot\Helpers\FileHelper;

/**
 * Manage entries in a CSV file
 */
abstract class CsvManager
{
    protected array $loadedData;
    protected bool $isDataLoaded;
    protected array $headers;
    private $lockFilePointer;

    protected function __construct($headers = [])
    {
        $this->loadedData = [];
        $this->isDataLoaded = false;
        $this->headers = $headers;
        $this->lockFilePointer = null;
    }

    protected abstract function getFilename(): string;
    protected abstract function useLocking(): bool;
    protected abstract function getLockFilename(): string;

    /**
     * Add the given member entries to the file
     * @return bool
     */
    protected function addFields(array $fields)
    {
        if ($this->useLocking())
            $this->lock();

        $this->loadFromFile(false);
        if (!$this->hasFieldsWithLocking($fields, false)) {
            $this->loadedData[] = $fields;
        }
        $this->saveToFile(false);

        if ($this->useLocking())
            $this->unlock();
    }

    /**
     * Remove the line with the specified fields from the file
     */
    protected function removeFields(array $fields)
    {
        if ($this->useLocking())
            $this->lock();

        $this->loadFromFile(false);
        $index = $this->findFields($fields, false);

        if ($index >= 0) {
            unset($this->loadedData[$index]);
            array_splice($this->loadedData, $index, 1);
            $this->saveToFile(false);
        }

        if ($this->useLocking())
            $this->unlock();
    }

    protected function isHeader(array $fields)
    {
        return self::fieldsMatch($fields, $this->headers);
    }

    /**
     * Whether or not the given member id is present in the list
     */
    public function hasFields(array $fields): bool
    {
        return $this->hasFieldsWithLocking($fields, $this->useLocking());
    }

    private function hasFieldsWithLocking(array $fields, bool $useLocking)
    {
        return $this->findFields($fields, $useLocking) >= 0;
    }

    /**
     * Return the index in the loaded data of this fields line or -1 if not found
     */
    protected function findFields(array $fields, bool $useLocking): int
    {
        if ($useLocking)
            $this->lock();

        if (!$this->isDataLoaded) {
            $this->loadFromFile(false);
        }

        $result = -1;
        $loadedDataCount = count($this->loadedData);
        for ($index = 0; $index < $loadedDataCount; ++$index) {
            if (self::fieldsMatch($fields, $this->loadedData[$index])) {
                $result = $index;
                break;
            }
        }

        if ($useLocking)
            $this->unlock();

        return $result;
    }

    private static function fieldsMatch(array $fieldsA, array $fieldsB): bool
    {
        $fieldsACount = count($fieldsA);
        $fieldsBCount = count($fieldsB);
        if ($fieldsACount === $fieldsBCount) {
            $match = true;
            for ($index = 0; $index < $fieldsBCount; ++$index) {
                $match &= $fieldsA[$index] == $fieldsB[$index];
            }
            if ($match == true) {
                return true;
            }
        }
        return false;
    }

    protected function lock()
    {
        $fileName = $this->getLockFilename();
        $this->lockFilePointer = FileHelper::lock($fileName);
    }

    protected function unlock()
    {
        $fileName = $this->getLockFilename();
        $this->lockFilePointer = FileHelper::unlock($this->lockFilePointer, $fileName);
    }

    protected function loadFromFile(bool $useLocking = true)
    {
        if ($useLocking)
            $this->lock();

        if (file_exists($this->getFilename())) {
            if (($handle = fopen($this->getFilename(), 'r')) !== false) {
                $headersLoaded = false;
                while (($fields = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                    if (count($fields)) {
                        if (!$headersLoaded && $this->isHeader($fields)) {
                            $headersLoaded = true;
                            continue;
                        }
                        if ($fields == null) {
                            //empty csv line
                            continue;
                        }
                        $this->loadedData[] = $fields;
                    }
                }
                fclose($handle);
            } else {
                throw new \ErrorException("Failed to open file");
            }
            $this->isDataLoaded = true;
        }

        if ($useLocking)
            $this->unlock();
    }

    protected function saveToFile(bool $useLocking = true)
    {
        if ($useLocking)
            $this->lock();

        if (($handle = fopen($this->getFilename(), 'w')) !== false) {
            fputcsv($handle, $this->headers, ',', '"', '\\');
            foreach ($this->loadedData as $fields) {
                fputcsv($handle, $fields, ',', '"', '\\');
            }
            fclose($handle);
        } else {
            throw new \ErrorException("Failed to open file");
        }

        if ($useLocking)
            $this->unlock();
    }
}
