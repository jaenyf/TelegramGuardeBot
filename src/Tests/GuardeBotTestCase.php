<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Tests;

require_once 'src/Requires.php';

use PHPUnit\Framework\TestCase;
use DI\Container;
use DI\ContainerBuilder;

use TelegramGuardeBot\App;
use TelegramGuardeBot\AppConfig;

class GuardeBotTestCase extends TestCase
{
    protected const appConfigFileName = "app.config.ci";


    public static function setupBeforeClass() : void
    {
        //Required for loading rbx files
        ini_set('memory_limit', '-1');
    }

    protected function setUp() : void
    {
        if(in_array("noSetUp", $this->getGroups()))
        {
            return;
        }

        if(!App::isInitialized())
        {
            App::initialize(self::appConfigFileName, $this->InitializeContainerWithMocks());
        }
    }

    protected function tearDown(): void
    {
        App::dispose();
    }

    protected function InitializeContainerWithMocks() : Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'logger' => $this->createMock(\Psr\Log\LoggerInterface::class),
            'bot' => $this->createMock(\TelegramGuardeBot\GuardeBot::class),
            'telegramApi' => $this->createMock(\TelegramGuardeBot\TelegramApi::class),
            'appConfig' => new AppConfig(self::appConfigFileName),
            'scheduler' => $this->createMock(\TelegramGuardeBot\Workers\Scheduler::class),
            'newMembersValidationManager' => $this->createMock(\TelegramGuardeBot\Managers\NewMembersValidationManager::class),
            \TelegramGuardeBot\Estimators\MlLanguageTextEstimator::class
                => $this->createMock(\TelegramGuardeBot\Estimators\MlLanguageTextEstimator::class),
            \TelegramGuardeBot\Estimators\MlSpamTextValidationEstimator::class
                => $this->createMock(\TelegramGuardeBot\Estimators\MlSpamTextValidationEstimator::class),
            \TelegramGuardeBot\Learners\MlHamTextLearner::class
                => $this->createMock(\TelegramGuardeBot\Learners\MlHamTextLearner::class),
            \TelegramGuardeBot\Learners\MlSpamTextLearner::class
                => $this->createMock(\TelegramGuardeBot\Learners\MlSpamTextLearner::class),
            \TelegramGuardeBot\Learners\MlLanguageTextLearner::class
                => $this->createMock(\TelegramGuardeBot\Learners\MlLanguageTextLearner::class)
        ]);

        return $builder->build();
    }
}
