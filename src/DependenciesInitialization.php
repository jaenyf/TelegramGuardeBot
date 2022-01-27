<?php

declare(strict_types=1);

namespace TelegramGuardeBot;

use Psr\Container\ContainerInterface;
use DI\Container;
use DI\ContainerBuilder;
use function DI\create as DIcreate;
use function DI\get as DI_get;
use function DI\factory as DI_factory;

use TelegramGuardeBot\AppConfig;
use TelegramGuardeBot\GuardeBot;
use TelegramGuardeBot\TelegramApi;

class DependenciesInitialization
{
    public static function InitializeContainer(string $configFileName, string $env = 'dev') : Container
    {
        $builder = new ContainerBuilder();

        if($env === 'prod'){
            $builder->enableCompilation(__DIR__ . '/tmp');
            $builder->writeProxiesToFile(true, __DIR__ . '/tmp/proxies');
        }

        $builder->addDefinitions([
            'logger' => DI_factory("TelegramGuardeBot\Log\GuardeBotLogger::getInstance"),
            'bot' => (function(ContainerInterface $c){
                return new GuardeBot($c->get('telegramApi'), $c->get('appConfig')->locale);
            }),
            'telegramApi' => (function(ContainerInterface $c){
                return new TelegramApi($c->get('appConfig')->botToken, $c->get('appConfig')->enableApiLogging);
            }),
            'appConfig' => new AppConfig($configFileName)
        ]);

        return $builder->build();
    }
}
