<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Workers;

use TelegramGuardeBot\App;
use TelegramGuardeBot\Workers\BackgroundProcessWorker;
use TelegramGuardeBot\Workers\BackgroundProcessInstanciator;
use TelegramGuardeBot\Workers\Task;
use TelegramGuardeBot\Helpers\FileHelper;

/**
 * A simple scheduler that run stacked tasks on demand
 */
class Scheduler extends BackgroundProcessWorker implements BackgroundProcessInstanciator
{
    private static Scheduler $instance;

    private const PersistedFilename = '.scheduler.dat';

    private static $lockFilePointer;
    private static int $lastUnserializationTime;

    private array $tasksList;

    private function __construct()
    {
        parent::__construct(null);
        $this->tasksList = [];

        $this->withLooping();
        $this->withSleepSeconds(1);

        self::$lastUnserializationTime = 0;
    }

    public function setUp() : void
    {
        App::initialize();
    }

    /**
     * Do not store the result of getInstance as it may be invalidated
     */
    public static function getInstance(): Scheduler
    {
        return self::getInstanceWithLocking(true);
    }

    /**
     * Do not store the result of getInstance as it may be invalidated
     */
    private static function getInstanceWithLocking(bool $shouldLock): Scheduler
    {
        if($shouldLock)
        {
            self::lock();
        }

        $instance = null;
        $unserialized = false;
        if (isset(self::$instance))
        {
            if (!Scheduler::isInvalidated())
            {
                $instance = self::$instance;
            }
            else if (file_exists(Scheduler::PersistedFilename))
            {
                $instance = self::unserialize();
                $unserialized = true;
            }
        }
        else if (file_exists(Scheduler::PersistedFilename))
        {
            $instance = self::unserialize();
            $unserialized = true;
        }
        else
        {
            $instance = new Scheduler();
        }

        self::$instance = $instance;

        if($shouldLock)
        {
            self::unlock();
        }

        if ($unserialized === true)
        {
            //It is important to ensure unlock is done before setting lastUnserializationTime, otherwise invalidation may take place while Scheduler is still locked
            self::$lastUnserializationTime = time();
        }

        return self::$instance;
    }


    public function addTask(Task $task)
    {
        if ($task->isStarted())
        {
            throw new \ErrorException('Can not add a started task');
        }

        self::lock();
        array_push(self::getInstanceWithLocking(false)->tasksList, $task);
        self::getInstanceWithLocking(false)->persist(false);
        self::unlock();
    }

    public function removeTaskById(string $taskId)
    {
        self::lock();
        $indexesToRemove = [];
        $instance = self::getInstanceWithLocking(false);
        $tasksCount = count($instance->tasksList);
        for ($index = 0; $index < $tasksCount; ++$index)
        {
            if ($instance->tasksList[$index]->getUid() == $taskId)
            {
                array_push($indexesToRemove, $index);
            }
        }
        foreach ($indexesToRemove as $indexToRemove)
        {
            unset($instance->tasksList[$indexToRemove]);
            array_splice($instance->tasksList, $indexToRemove, 1);
        }
        $instance->persist(false);
        self::unlock();
    }

    public function do()
    {
        //Check for invlidation as there may be new tasks not loaded
        App::getInstance()->getScheduler()->runDueTasks();
    }

    /**
     * Wait until all tasks are completed
     */
    public function waitForAllTasksCompletion()
    {
        do
        {
            if(count($this->getTasks()) > 0)
            {
                sleep(1);
            }
            else
            {
                break;
            }

        } while(true);
    }

    private function getTasks(): array
    {
        return $this->tasksList;
    }

    public function getBackgroundProcess(string $uid): BackgroundProcessWorker
    {
        self::lock();

        $resultTask = null;
        foreach ($this->getTasks() as $task)
        {
            if ($task->getUid() == $uid)
            {
                $resultTask = $task;
                break;
            }
        }

        self::unlock();
        return $resultTask;
    }

    /**
     * Run all the due tasks
     */
    private function runDueTasks()
    {
        self::lock();
        $dueTasks = [];
        foreach (self::getInstanceWithLocking(false)->getTasks() as $task)
        {
            if (!$task->isStarted() && !$task->isDone() && $task->getNextRunTime() <= time())
            {
                array_push($dueTasks, $task);
            }
        }
        self::unlock();

        foreach ($dueTasks as $task)
        {
            $task->start();
        }
    }

    public static function lock()
    {
        self::$lockFilePointer = FileHelper::lock(Scheduler::PersistedFilename . '.lock');
    }

    public static function unlock()
    {
        self::$lockFilePointer = FileHelper::unlock(self::$lockFilePointer, Scheduler::PersistedFilename . '.lock');
    }

    //Made public for Background Task auto-code
    private function persist($shouldLock = true)
    {
        if ($shouldLock)
        {
            self::lock();
        }

        $serialized = serialize($this);
        $file = fopen(Scheduler::PersistedFilename, 'w');
        if(false !== $file){
            fwrite($file, $serialized);
            fclose($file);
        }

        if ($shouldLock)
        {
            self::unlock();
        }
    }

    private static function unserialize()
    {
        $instance = unserialize(file_get_contents(Scheduler::PersistedFilename));
        return $instance;
    }

    private static function isInvalidated(): bool
    {
        if (!file_exists(Scheduler::PersistedFilename))
        {
            return false;
        }
        return self::$lastUnserializationTime < filemtime(Scheduler::PersistedFilename);
    }
}
