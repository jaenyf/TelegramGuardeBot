<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Managers\Spams;

class SpamAuthorsManager
{
    private array $loadedAuthors;
    private const GlobalBlackListFileName = 'Spammers.lst';
    private const Headers = ['ID', 'USERNAME', 'FIRST_NAME', 'LAST_NAME'];

    public function __construct()
    {
        $this->loadedAuthors = [];
    }

    /**
     * Add the given spam author entries to the global blacklist
     * @return bool
     */
    public function addGlobal(
        string $id,
        string $userName,
        string $firstName,
        string $lastName
    )
    {
        $this->loadFromFile();
        if(!$this->hasUserId($id))
        {
            $this->loadedAuthors[] = [$id, $userName, $firstName, $lastName];
        }
        $this->saveToFile();
    }

    private function isHeader($csvLineData)
    {
        return $csvLineData[0] == SpamAuthorsManager::Headers[0];
    }

    private function hasUserId($userId) : bool
    {
        foreach($this->loadedAuthors as $author)
        {
            if($author[0] == $userId)
            {
                return true;
            }
        }
        return false;
    }

    private function loadFromFile()
    {
        if (($handle = fopen(SpamAuthorsManager::GlobalBlackListFileName, 'c+')) !== FALSE) {
            while (($csvLineData = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE) {
                $count = count($csvLineData);
                if($count)
                {
                    if($this->isHeader($csvLineData))
                    {
                        continue;
                    }
                    $this->loadedAuthors[] = $csvLineData;
                }
            }
            fclose($handle);
        }
    }

    private function saveToFile()
    {
        if (($handle = fopen(SpamAuthorsManager::GlobalBlackListFileName, 'w')) !== FALSE) {
            
            fputcsv($handle, SpamAuthorsManager::Headers, ',', '"', '\\');
            foreach ($this->loadedAuthors as $fields) {
                fputcsv($handle, $fields, ',', '"', '\\');
            }
            fclose($handle);
        }
    }
}