<?php

declare(strict_types=1);

namespace TelegramGuardeBot;

use DI\Container;
use DI\ContainerBuilder;
use function DI\create as DIcreate;
use function DI\get as DI_get;
use function DI\factory as DI_factory;

class DependenciesInitialization
{
    public static function InitializeContainer(string $env = 'dev') : Container
    {
        $builder = new ContainerBuilder();

        if($env === 'prod'){
            $builder->enableCompilation(__DIR__ . '/tmp');
            $builder->writeProxiesToFile(true, __DIR__ . '/tmp/proxies');
        }

        $builder->addDefinitions([
            'logger' => DI_factory("TelegramGuardeBot\Log\GuardeBotLogger::getInstance")
        ]);

        return $builder->build();
    }
}
