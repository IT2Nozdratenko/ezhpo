<?php

/**
 * фанк...
 */
if(!function_exists('user'))
{
    function user()
    {
        return auth()->user();
    }
}


if(!function_exists('getValueByPriority'))
{
    function getValueByPriority(...$args)
    {
        foreach ($args as $arg) {
            if ($arg) {
                return $arg;
            }
        }

        return null;
    }
}

if(!function_exists('convertStringToBoolean'))
{
    function convertStringToBoolean(?string $string): bool
    {
        if ($string) {
            return mb_strtolower($string) !== 'нет';
        }

        return false;
    }
}
