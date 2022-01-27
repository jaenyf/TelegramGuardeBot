<?php

declare(strict_types=1);

namespace TelegramGuardeBot;


final class AppConfig
{
    public const defaultConfigFileName = 'app.config';

    public string $envName;
    public string $botToken;
    public int $logChatId;
    public string $locale;
    public bool $enableApiLogging;
    public array $messagesActions;
    public bool $enableNewMemberValidation;
    public int $newMemberValidationTimeout;

    public function __construct(string $configFileName = self::defaultConfigFileName)
    {
        if (!file_exists($configFileName))
        {
            throw new \ErrorException('Configuration file not found');
        }

        $config = json_decode(self::stripComments(file_get_contents($configFileName)), false);

        $this->envName = $config->envName;
        $this->botToken = $config->botToken;
        $this->logChatId = $config->logChatId;
        $this->locale = strtoupper($config->locale);
        $this->enableApiLogging = $config->enableApiLogging;
        $this->messagesActions = $config->messagesActions;
        $this->enableNewMemberValidation = $config->enableNewMemberValidation ?? false;
        $this->newMemberValidationTimeout = $config->newMemberValidationTimeout;


        if (empty($this->locale))
        {
            throw new \ErrorException('Missing locale');
        }

        switch ($this->locale)
        {
            case 'FR':
            case 'EN':
                break;
            default:
                throw new \ErrorException('Unsupported locale');
        }
        require_once fromAppSource('/i18n/GuardeBotMessages_' . $this->locale . '.php');
    }

    /**
     * From https://stackoverflow.com/a/10252511/319266
     * @param string $str
     * @return string
     */
    private static function stripComments($text)
    {
        return preg_replace('![ \t]*//.*[ \t]*[\r\n]!', '', $text);
    }
}
