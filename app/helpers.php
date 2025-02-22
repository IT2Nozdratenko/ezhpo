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
if (! function_exists('dd500')) {
    function dd500(...$args)
    {
        http_response_code(500);
        foreach ($args as $arg) {
            dump($arg);
        }
        die(1);
    }
}