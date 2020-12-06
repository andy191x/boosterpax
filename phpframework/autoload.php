<?php

//
// Includes
//

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/phpframework/utility.php');

//
// Script logic
//

phpframeworkRegisterAutoload();

//
// Global routines
//

function phpframeworkRegisterAutoload()
{
    static $registered = false;

    if (!$registered)
    {
        spl_autoload_register('phpframeworkAutoload');
        $registered = true;
    }
}

function phpframeworkAutoload($class)
{
    $class_lower = mb_strtolower($class);
    
    if (phpframework_try_autoload(__DIR__ . '/phpframework', $class_lower))
    {
        return true;
    }

    return false;
}

/**
 * @param string $path
 * @param string $class
 * @return bool
 */
function phpframework_try_autoload($path, $class)
{
    $target_file = $path . '/' . $class . '.php';

    if (@file_exists($target_file) && @is_file($target_file))
    {
        require_once($target_file);
        return true;
    }

    return false;
}
