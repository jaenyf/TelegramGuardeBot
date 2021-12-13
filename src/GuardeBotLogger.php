<?php

namespace TelegramGuardeBot;

/**
 * GuardeBot Logger Class.
 *
 * @author jaenyf
 */
class GuardeBotLogger
{
    private static $self = null;

    /*
     * The maximum level of deepness;
     */
    private $maxDeepLevel = 128;

    /**
     * Prints the list of parameters from/to Telegram's API endpoint
     * \param $element element as array
     */
    public static function log($element, $title=null)
    {
        try
        {
            if(!isset(self::$self))
            {
                self::$self = new GuardeBotLogger();
            }
            
            $e = new \Exception();
            $message = PHP_EOL;
            $message .= '=========[Element]=========';
            if(!empty($title))
            {
                $message .= PHP_EOL;
                $message .= '### ' . $title. ' ###';
            }
            $message .= PHP_EOL;
            $message .= self::$self->rt($element);
            $message .= PHP_EOL;
            $message .= '=========[Trace]============';
            $message .= PHP_EOL;
            $message .= $e->getTraceAsString();
            self::_log_to_file($message);
            echo str_replace(' ', '&nbsp;', str_replace(PHP_EOL, '<br/>', $message));
            return $message;
        }
        catch (\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /// Write a string in the log file adding the current server time
    /**
     * Write a string in the log file GuardeBotLogger.txt adding the current server time
     * \param $text the text to append in the log.
     */
    private static function _log_to_file($text)
    {
        try {
            $dir_name = 'logs';
            if (!is_dir($dir_name)) {
                mkdir($dir_name);
            }
            $fileName = $dir_name . '/' . __CLASS__ . '-' . date('Y-m-d') . '.txt';
            $myFile = fopen($fileName, 'a+');
            $date = '============[Date]============';
            $date .= "\n";
            $date .= '[ ' . date('Y-m-d H:i:s  e') . ' ] ';
            fwrite($myFile, $date . $text . "\n\n");
            fclose($myFile);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    private function appendNewLineAndIndent(&$text, $level)
    {
        $text .= PHP_EOL;
        $this->indent($text, $level);
        return $text;
    }

    private function indent(&$text, $level)
    {
        $text .= str_pad('', $level, ' ', STR_PAD_LEFT);
    }

    private function logArrayOrObject($element, $level, $type)
    {
        $text = '';
        

        $keys = [];
        $values = [];
        $keysCount = 0;
        foreach ($element as $key => $value) {
            array_push($keys, $key);
            array_push($values, $value);
            ++$keysCount;
        }


        if($keysCount === 0)
        {
            //empty array or object
            $text .=  $this->getOpeningSeparatorByType($type);
            $text .= $this->getClosingSeparatorByType($type);
            $this->appendNewLineAndIndent($text, $level);
            return $text;
        }

        $text .=  $this->getOpeningSeparatorByType($type);

        for ($i = 0; $i < $keysCount; $i++) {
            $key = $keys[$i];
            $value = $values[$i];
            $this->appendNewLineAndIndent($text, $level);;
            $text .= ($this->rt($key, $level +1 ) . ' : ' . self::$self->rt($value, $level +1, true) . ', ');
        }

        $text = substr($text, 0, -2);
        $this->appendNewLineAndIndent($text, $level);
        $text .= $this->getClosingSeparatorByType($type);

        return $text;
    }

    private function getOpeningSeparatorByType($type)
    {
        switch($type)
        {
            case 'array' : return '[';
            case 'object' : return '{';
        }
        return '';
    }

    private function getClosingSeparatorByType($type)
    {
        switch($type)
        {
            case 'array' : return ']';
            case 'object' : return '}';
        }
        return '';
    }

    private function rt($element, $level = 0, $skipIndent = false)
    {
        $text = '';
        if($level >= $this->maxDeepLevel)
        {
            return $text;
        }
        
        if(!$skipIndent)
        {
            $this->indent($text, $level);
        }
        if ($element instanceof \CURLFile) {
            $text .= ' - CURLFile = File' . PHP_EOL;
        } else {
            $type = gettype($element);
            switch ( $type) {
                case 'array':
                case 'object':
                    $text .= $this->logArrayOrObject($element, $level,  $type);
                    break;
                case 'boolean':
                    $text .= $element ? 'true' : 'false';
                    break;
                case 'double':
                case 'integer':
                    $text .= $element;
                    break;
                case 'NULL':
                    $text .= 'NULL';
                    break;
                case 'resource':
                    $text .= '<resource>';
                    break;
                case 'resource (closed)':
                    $text .= '<resource (closed)>';
                    break;
                case 'unknown type':
                    $text .= '<unknown type>';
                    break;
                default:
                    $text .= ('\'' . str_replace('\'', '\\\'', $element) . '\'' );
                    break;
            }
        }
        return $text;
    }
}
