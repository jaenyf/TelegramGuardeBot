<?php

/**
 * GuardeBot Logger Class.
 *
 * @author jaenyf
 */
class GuardeBotLogger
{
    private static $self;

    /**
     * Prints the list of parameters from/to Telegram's API endpoint
     * \param $element element as array
     */
    public static function log($element)
    {
        try {
            self::$self = new self();
            $e = new \Exception();
            $message = PHP_EOL;
            $array = '=========[Element]==========';
            $array .= "\n";
            $message = self::$self->rt($element, 'root', true);
            $backtrace = '============[Trace]===========';
            $backtrace .= "\n";
            $backtrace .= $e->getTraceAsString();
            self::$self->_log_to_file($message . $array . $backtrace);
            return $message;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /// Write a string in the log file adding the current server time
    /**
     * Write a string in the log file GuardeBotLogger.txt adding the current server time
     * \param $text the text to append in the log.
     */
    private function _log_to_file($text)
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

    private function rt($element, $title = null, $head = true)
    {
        $ref = 'ref';
        $text = '';
        if ($head) {
            $text = "[$ref]";
            $text .= "\n";
        }
        if ($element instanceof CURLFile) {
            $text .= $ref . ' - CURLFile = File' . PHP_EOL;
        } else {
            switch (gettype($element)) {
                case 'array':
                case 'object':
                    foreach ($element as $key => $value) {
                        $text .= '[' . $key . '] => ' . self::$self->rt($value) . PHP_EOL . PHP_EOL;
                    }
                    break;
                case 'boolean':
                    $text .= $element ? 'true' : 'false';
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
                    $text .= $element;
                    break;
            }
        }
        return $text;
    }
}
