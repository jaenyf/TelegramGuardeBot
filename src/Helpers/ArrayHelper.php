<?php

namespace TelegramGuardeBot\Helpers;

/**
 * GuardeBot Class.
 *
 * @author jaenyf
 */
class ArrayHelper
{
    /**
     * Deeply convert an array to an object
     */
    public static function toObject($array)
    {
        if (!isset($array)) {
            return null;
        }

        $obj = (object)$array;
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = self::toObject($v);
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }
}
