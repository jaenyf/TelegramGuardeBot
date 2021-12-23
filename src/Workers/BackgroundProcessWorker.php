<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Workers;

include_once 'Support/Str.php';

/**
 * This class is used to handle background worker on a non-threaded environment
 * It consists of a main loop with a default sleep time where custom "do" code will be executed
 * It works by wrapping and pushing overrided "do" method code in a ".proc.php" file and execute it on a background process
 * The created .proc.php file can be deleted to stop worker execution
 * The .proc.php file is touched at each loop iteration to help tracking last execution time
 */
abstract class BackgroundProcessWorker
{
    //
    // This class is a bit of hacking
    // We have to use protected properties and methods instead of private for Reflection to be able to retrieve them
    //
    protected bool $isSingleRun;
    protected int $sleepSeconds;
    private bool $isStarted;
    private string $uid;

    protected function __construct()
    {
        $this->isSingleRun = false;
        $this->sleepSeconds = 3;
        $this->isStarted = false;
        $this->uid = uniqid();
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    public function withSingleRun(): BackgroundProcessWorker
    {
        $this->isSingleRun = true;
        return $this;
    }

    public function withLooping(): BackgroundProcessWorker
    {
        $this->isSingleRun = false;
        return $this;
    }

    public function withSleepSeconds(int $sleepSeconds)
    {
        $this->sleepSeconds = $sleepSeconds;
        return $this;
    }

    /**
     * Override this function with the code the worker has to do
     */
    public abstract function do();

    /**
     * Return the complete file path of our .proc file
     */
    protected function getProcFilePath(): string
    {
        return '_worker-' . $this->getUid() . '.proc.php';
    }

    protected function canStillRun(): bool
    {
        if ($this->isSingleRun) {
            return false;
        }

        return file_exists($this->getProcFilePath());
    }

    private function innerStart()
    {
        do {
            touch(__FILE__);
            $this->do();
            sleep($this->sleepSeconds);
        } while ($this->canStillRun());
        $this->stop();
    }

    public function stop()
    {
        if (file_exists($this->getProcFilePath())) {
            unlink($this->getProcFilePath());
        }
        $this->isStarted = false;
    }

    public function start()
    {
        if ($this->isStarted()) {
            throw new \ErrorException('Already started');
        }

        $procFilePath = $this->getProcFilePath();

        $reflectionClass = new \ReflectionClass($this);
        $className = $reflectionClass->getName();

        $foudndUses = [];
        foreach (file($reflectionClass->getFileName()) as $lineIndex => $line) {
            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, 'use ')) {
                array_push($foudndUses, $trimmedLine);
            }
        }

        $avoidConstruction = $reflectionClass->hasMethod('getInstance');

        $loggerClass = new \ReflectionClass('TelegramGuardeBot\\Log\\GuardeBotLogger');

        $bodyText = self::extractCode($this, 'innerStart', 0, !$avoidConstruction);
        $bodyText = '<?php' . "\r\n"
            . '//Created by ' . $className . "\r\n\r\n"
            . 'ini_set(\'display_errors\', \'1\');' . "\r\n"
            . 'error_reporting(E_ALL);' . "\r\n"
            . 'require_once str_replace(\'\\\\\', \'/\',__DIR__) . \'/../vendor/autoload.php\';' . "\r\n"
            . 'include_once \''. str_replace('\\', '/', $reflectionClass->getFileName()).'\';' . "\r\n"
            . implode("\r\n", $foudndUses) . "\r\n"
            . 'use ' . $className . ';' . "\r\n"
            . ($avoidConstruction ? '' : '$instance = ' . self::createBestConstructorCall($this, $reflectionClass)) . "\r\n"
            . $loggerClass->name . '::getInstance();' . "\r\n"
            . $bodyText;


        $bodyText = str_replace('$this', $avoidConstruction ? $className . '::getInstance()' : '$instance', $bodyText);

        $scriptFile = fopen($procFilePath, 'w');
        if ($scriptFile === false) {
            return false;
        }

        //Note: Avoid using PHP_BINARY constant here as it may result in strange behavior if it uses the php cgi version:
        //See: https://bugs.php.net/bug.php?id=24759
        $command = 'php -f "' . $procFilePath . '" ';
        $isUnixCommand = true;
        if (substr(php_uname(), 0, 7) == "Windows"){
            $command = 'start /B '. $command;
            $isUnixCommand = false;
        }
        else {
            $command = $command . " >/dev/null >&- >/dev/null &";
        }

        $bodyText = $bodyText . "\r\n\r\n" . '//started with: ' . $command . "\r\n\r\n";
        $bodyText = $bodyText . "\r\n" . '?>' . "\r\n";

        if (false === fwrite($scriptFile, $bodyText)) {
            fclose($scriptFile);
            return false;
        }

        if (false === fclose($scriptFile)) {
            return false;
        }


        if (!$isUnixCommand){
            pclose(popen($command, "r"));
        }
        else {
            exec($command);
        }


        if (false === exec($command)) {
            return false;
        }

        $this->isStarted = true;
        return true;
    }

    private static function createBestConstructorCall(BackgroundProcessWorker $instance, \ReflectionClass $class)
    {
        $ctor = $class->getConstructor();
        $arguments = [];
        foreach ($ctor->getParameters() as $parameter) {
            if ($class->hasProperty($parameter->name)) {
                $property = $class->getProperty($parameter->name);
                $property->setAccessible(true);
                array_push($arguments, json_encode($property->getValue($instance), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS));
            }
        }

        return 'new ' . $class->name . '(' . implode(',', $arguments) . ');';
    }

    private static function extractCode(object $instance, string $name, int $deepLevel, bool $declareGlobalInstance = false): string
    {
        $func = new \ReflectionMethod($instance, $name);
        $start_line = $func->getStartLine() - 1;
        $length = $func->getEndLine() - $start_line;

        $source = file($func->getFileName());
        $bodyText = implode("", array_slice($source, $start_line, $length));

        //rework the text to remove function modifiers and braces, and just output the function name
        $bodyText = trim($bodyText);
        $leftBracePos = strpos($bodyText, '{');
        $rightBracePos = strrpos($bodyText, '}');
        $bodyText =  substr($bodyText, $leftBracePos + 1, $rightBracePos);
        $bodyText = trim(rtrim($bodyText, '}'));

        $reflectionClass = new \ReflectionClass($instance);
        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->isPublic()) {
                continue;
            }

            if ($method->isConstructor() || $method->isDestructor() || $method->isStatic() || $method->isAbstract()) {
                continue;
            }

            if (str_starts_with($method->name, 'get')) {
                $method->setAccessible(true);
                $methodResult = $method->invoke($instance);
                $bodyText = str_replace('$this->' . $method->name . '()', json_encode($methodResult), $bodyText);
            } else
            if ($deepLevel === 0 && $method->name !== 'start' && $method->name !== 'do') {
                $codeText = static::extractCode($instance, $method->name, $deepLevel + 1);
                $bodyText = str_replace('$this->' . $method->name . '(' . implode(',', array_map(function ($methodParam) {
                    return '$' . $methodParam->name . ($methodParam->isDefaultValueAvailable() ? ' = ' . json_encode($methodParam->getDefaultValue()) : '');
                }, $method->getParameters())) . ')', $codeText, $bodyText);
            }
        }

        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $propertyResult = $property->isInitialized($instance) ? $property->getValue($instance) : null;
            $bodyText = str_replace('$this->' . $property->name, json_encode($propertyResult), $bodyText);
        }

        foreach ($reflectionClass->getConstants() as $constantName => $constantValue) {
            $className = $reflectionClass->getName();
            $bodyText = str_replace(substr($className, strrpos($className, '\\') + 1) . '::' . $constantName, json_encode($constantValue), $bodyText);
        }

        //wrap our function in an anonymous function for a direct call
        $bodyText = '(function(){' . (($declareGlobalInstance && $deepLevel == 0) ? 'global $instance;' : '') . $bodyText . '})()';

        //append a last semicolon
        if ($deepLevel === 0) {
            $bodyText .= ';';
        }

        return $bodyText;
    }
}
