<?php

//
// Includes
//

// ...

//
// Global routines
//

/**
 * Returns whether or not a file exists and is a file object.
 * @param string $path
 * @return bool
 */
function file_exists_file($path)
{
    return (@file_exists($path) && @is_file($path));
}

/**
 * Returns whether or not a file exists and is a folder object.
 * @param string $path
 * @return bool
 */
function file_exists_folder($path)
{
    return (@file_exists($path) && @is_dir($path));
}