<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers;

use TelegramGuardeBot\Managers\CsvManager;

/**
 * Manage members entries in a CSV file
 */
abstract class CsvMembersManager extends CsvManager
{
    private const Headers = ['ID', 'USERNAME', 'FIRST_NAME', 'LAST_NAME'];

    protected function __construct()
    {
        parent::__construct(CsvMembersManager::Headers);
    }

    protected function useLocking(): bool
    {
        return false;
    }

    /**
     * Add the given member entries to the file
     * @return bool
     */
    public function add(
        $id,
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

    /**
     * Whether or not the given member id is present in the list
     */
    public function has($id): bool
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
}
