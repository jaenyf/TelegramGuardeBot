<?php

declare(strict_types=1);

namespace TelegramGuardeBot;

use Psr\Container\ContainerInterface;
use DI\Container;
use DI\ContainerBuilder;
use function DI\create as DI_create;
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
            $builder->enableCompilation('./tmp');
            $builder->writeProxiesToFile(true, './tmp/proxies');
        }

        $builder->addDefinitions([
            'logger' => DI_factory("TelegramGuardeBot\Log\GuardeBotLogger::getInstance"),
            'bot' => (function(ContainerInterface $c){
                return new GuardeBot($c->get('telegramApi'), $c->get('appConfig')->locale);
            }),
            'telegramApi' => (function(ContainerInterface $c){
                return new TelegramApi($c->get('appConfig')->botToken, $c->get('appConfig')->enableApiLogging);
            }),
            'appConfig' => new AppConfig($configFileName),
            //TODO: check DI_factory does not cache getInstance as, it should be called every time because it may get invalidated by new tasks adds/removals
            'scheduler' => DI_factory("TelegramGuardeBot\Workers\Scheduler::getInstance"),
            'newMembersValidationManager' => DI_create(\TelegramGuardeBot\Managers\NewMembersValidationManager::class),
            \TelegramGuardeBot\Estimators\MlLanguageTextEstimator::class
                => DI_create(\TelegramGuardeBot\Estimators\MlLanguageTextEstimator::class),
            \TelegramGuardeBot\Estimators\MlSpamTextValidationEstimator::class
                => DI_create(\TelegramGuardeBot\Estimators\MlSpamTextValidationEstimator::class),
            \TelegramGuardeBot\Learners\MlHamTextLearner::class
                => DI_create(\TelegramGuardeBot\Learners\MlHamTextLearner::class),
            \TelegramGuardeBot\Learners\MlSpamTextLearner::class
                => DI_create(\TelegramGuardeBot\Learners\MlSpamTextLearner::class),
            \TelegramGuardeBot\Learners\MlLanguageTextLearner::class
                => DI_create(\TelegramGuardeBot\Learners\MlLanguageTextLearner::class)
        ]);

        return $builder->build();
    }
}
