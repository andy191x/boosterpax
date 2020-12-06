<?php

//
// Includes
//

// ...

//
// Global routines
//

/**
 * Encode a string for an HTML document.
 * @param string $val
 * @return string
 */
function html_encode($val)
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8', true);
}

/**
 * PHP string to escaped JS single quoted literal.
 * @param mixed $val
 * @param bool $quote
 * @return string
 */
function jssq_encode($val, $quote = true)
{
    $enc = (string)$val;
    $enc = preg_replace("/\n/", "\\n", $enc);
    $enc = preg_replace("/\r/", "\\r", $enc);
    $enc = preg_replace("/\\'/", "\\'", $enc);

    if ($quote)
    {
        return "'" . $enc . "'";
    }

    return $enc;
}

/**
 * PHP string to escaped JS double quoted literal.
 * @param mixed $val
 * @param bool $quote
 * @return string
 */
function jsdq_encode($val, $quote = true)
{
    if ($quote)
    {
        return '"' . preg_replace('/\"/', '\\"', (string)$val) . '"';
    }

    return preg_replace('/\"/', '\\"', (string)$val);
}

/**
 * Syntatic sugar for PHP arrays indented to be used at associative arrays (maps)
 * @return array
 */
function map()
{
    return array();
}

/**
 * Wrapper for explode. If the string is an empty string, it returns an empty array. Explode would normally return an array with a 0-length string.
 * @param $delimiter
 * @param $string
 * @param null $limit
 * @return array
 */
function explode_empty($delimiter, $string, $limit = null)
{
    if (strlen($string) == 0)
    {
        return array();
    }

    if ($limit !== null)
    {
        return explode($delimiter, $string, $limit);
    }

    return explode($delimiter, $string);
}

/**
 * Joins two strings together with only a single divider.
 * @param string $left
 * @param string $right
 * @param string $divider
 */
function join_path($left, $right, $divider)
{
    return mb_rtrim($left, $divider) . $divider . mb_ltrim($right, $divider);
}

/**
 * @param string $path
 * @param string $class
 * @return bool
 */
function try_autoload($path, $class)
{
    $target_file = $path . '/' . $class . '.php';

    if (file_exists_file($target_file))
    {
        require_once($target_file);
        return true;
    }

    return false;
}
