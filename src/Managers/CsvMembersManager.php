<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers;

/**
 * Manage members entries in a CSV file
 */
abstract class CsvMembersManager
{
    private static CsvMembersManager $instance;
    private array $loadedData;
    private bool $isDataLoaded;
    private const Headers = ['ID', 'USERNAME', 'FIRST_NAME', 'LAST_NAME'];

    protected function __construct()
    {
        $this->loadedData = [];
        $this->isDataLoaded = false;
    }

    protected static abstract function createInstance(): CsvMembersManager;
    protected abstract function getFilename(): string;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = static::createInstance();
        }
        return self::$instance;
    }

    /**
     * Add the given member entries to the file
     * @return bool
     */
    public function add(
        int|string $id,
        string $userName,
        string $firstName,
        string $lastName
    ) {
        $this->loadFromFile();
        if (!$this->has($id)) {
            $this->loadedData[] = [$id, $userName, $firstName, $lastName];
        }
        $this->saveToFile();
    }

    private function isHeader($csvLineData)
    {
        return $csvLineData[0] == CsvMembersManager::Headers[0];
    }

    /**
     * Whether or not the given member id is present in the list
     */
    public function has(int|string $id): bool
    {
        if (!$this->isDataLoaded) {
            $this->loadFromFile();
        }

        foreach ($this->loadedData as $data) {
            if ($data[0] == $id) {
                return true;
            }
        }
        return false;
    }

    private function loadFromFile()
    {
        if (($handle = fopen($this->getFilename(), 'c+')) !== FALSE) {
            while (($fields = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE) {
                if (count($fields)) {
                    if ($this->isHeader($fields)) {
                        continue;
                    }
                    if ($fields == NULL) {
                        //empty csv line
                        continue;
                    }
                    $this->loadedData[] = $fields;
                }
            }
            fclose($handle);
            $this->isDataLoaded = true;
        }
    }

    private function saveToFile()
    {
        if (!$this->isDataLoaded) {
            $this->loadFromFile();
        }

        if (($handle = fopen($this->getFilename(), 'w')) !== FALSE) {

            fputcsv($handle, CsvMembersManager::Headers, ',', '"', '\\');
            foreach ($this->loadedData as $fields) {
                fputcsv($handle, $fields, ',', '"', '\\');
            }
            fclose($handle);
        }
    }
}
