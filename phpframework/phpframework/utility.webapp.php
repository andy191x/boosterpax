<?php

//
// Includes
//

require_once(__DIR__ . '/utility.unicode.php');

//
// Global routines
//

/**
 * 302 temporary redirect (server side redirect)
 * @param $url
 * @param bool $exit
 */
function redirect($url, $exit = true)
{
    header('Location: ' . $url);

    if ($exit)
    {
        exit();
    }
}

/**
 * 301 permanent redirect, use with care!
 * @param string $url
 * @param bool $exit
 */
function redirect_301($url, $exit = true)
{
    http_add_status_line(301, 'Moved Permanently');
    header('Location: ' . $url);

    if ($exit)
    {
        exit();
    }
}

/**
 * 404 error
 */
function error_404()
{
    http_add_status_line(404, 'Not Found');
}

/**
 * 500 error
 */
function error_500()
{
    http_add_status_line(500, 'Internal Server Error');
}

/**
 * Adds the HTTP status line based on the current protocol.
 * https://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html
 * @param int $code
 * @param string $reason
 */
function http_add_status_line($code, $reason)
{
    $protocol = 'HTTP/1.1';
    if (isset($_SERVER['SERVER_PROTOCOL']) && is_string($_SERVER['SERVER_PROTOCOL']))
    {
        $protocol = (string)$_SERVER['SERVER_PROTOCOL'];
    }

    header($protocol . ' ' . $code . ' ' . $reason);
}

/**
 * Returns the HTTP method of the current request in lowercase text.
 * @return string
 */
function client_http_method()
{
    $method = 'get';

    if (isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']))
    {
        $server_method = strtolower((string)$_SERVER['REQUEST_METHOD']);

        // https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
        foreach (array('get', 'options', 'head', 'post', 'put', 'delete', 'trace', 'connect') as $valid_method)
        {
            if ($server_method === $valid_method)
            {
                $method = $valid_method;
                break;
            }
        }
    }

    return $method;
}

/**
 * Returns the URI of the current request
 * @return string
 */
function client_uri()
{
    $uri = '';

    if (isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI']))
    {
        $uri = mb_trim(utf8_sanitize((string)$_SERVER['REQUEST_URI']));
    }

    return $uri;
}

/**
 * Triggers a PHP warning and adds a line to the webserver log. The running script continues to execute. This error is never displayed to the user.
 * @param string $warning
 */
function php_warning($warning)
{
    trigger_error($warning, E_USER_WARNING);
}

/**
 * Triggers a PHP error that ends the running script. This will render a blank page (HTTP 200) and add a line to the webserver log. This error is never displayed to the user.
 * @param string $error
 */
function php_error($error)
{
    trigger_error($error, E_USER_ERROR);
    exit(); // should never be reached
}

//
// Global user-input parsing routines
//

/**
 * @param string $key
 * @return bool
 */
function has_get_var($key)
{
    return (isset($_GET[$key]) && is_string($_GET[$key]));
}

/**
 * @param string $key
 * @param string $default
 * @param int $max_length
 * @return string
 */
function get_get_string($key, $default = '', $max_length = 0)
{
    $val = $default;

    if (isset($_GET[$key]) && is_string($_GET[$key]))
    {
        $val = utf8_sanitize((string)$_GET[$key]);

        if ($max_length > 0)
        {
            $val = mb_substr($val, 0, $max_length);
        }
    }

    return $val;
}

/**
 * @param string $key
 * @param int $default
 * @return int
 */
function get_get_int($key, $default = 0)
{
    if (!isset($_GET[$key]))
    {
        return $default;
    }

    $intstr = get_get_string($key, '0', 64);
    return (int)intval($intstr);
}

/**
 * @param string $key
 * @param float $default
 * @return float
 */
function get_get_float($key, $default = 0.0)
{
    if (!isset($_GET[$key]))
    {
        return $default;
    }

    $floatstr = get_get_string($key, '0.0', 64);
    return (float)floatval($floatstr);
}

/**
 * @param string $key
 * @return bool
 */
function has_post_var($key)
{
    return (isset($_POST[$key]) && is_string($_POST[$key]));
}

/**
 * @param string $key
 * @param string $default
 * @param int $max_length
 * @return string
 */
function get_post_string($key, $default = '', $max_length = 0)
{
    $val = $default;

    if (isset($_POST[$key]) && is_string($_POST[$key]))
    {
        $val = utf8_sanitize((string)$_POST[$key]);

        if ($max_length > 0)
        {
            $val = mb_substr($val, 0, $max_length);
        }
    }

    return $val;
}

/**
 * @param string $key
 * @param int $default
 * @return int
 */
function get_post_int($key, $default = 0)
{
    if (!isset($_POST[$key]))
    {
        return $default;
    }

    $intstr = get_post_string($key, 64);
    return (int)intval($intstr);
}

/**
 * @param string $key
 * @param float $default
 * @return float
 */
function get_post_float($key, $default = 0.0)
{
    if (!isset($_POST[$key]))
    {
        return $default;
    }

    $floatstr = get_post_string($key, 64);
    return (float)floatval($floatstr);
}

/**
 * @param string $name
 * @return bool
 */
function has_file_upload($name)
{
    return isset($_FILES[$name]) &&
    isset($_FILES[$name]['name']) &&
    isset($_FILES[$name]['type']) &&
    isset($_FILES[$name]['tmp_name']) &&
    isset($_FILES[$name]['error']) &&
    isset($_FILES[$name]['size']);
}

/**
 * @param string $name
 * @param string $key
 * @return bool
 */
function has_file_upload_var($name, $key)
{
    return isset($_FILES[$name]) && isset($_FILES[$name][$key]);
}

/**
 * @param string $name
 * @param string $key
 * @param string $default
 * @param int $max_length
 * @return string
 */
function get_file_upload_string($name, $key, $default = '', $max_length = 0)
{
    $val = $default;

    if (isset($_FILES[$name]) && isset($_FILES[$name][$key]) && is_string($_FILES[$name][$key]))
    {
        $val = utf8_sanitize((string)$_FILES[$name][$key]);

        if ($max_length > 0)
        {
            $val = mb_substr($val, 0, $max_length);
        }
    }

    return $val;
}

/**
 * @param string $name
 * @param string $key
 * @param int $default
 * @return int
 */
function get_file_upload_int($name, $key, $default = 0)
{
    $val = $default;

    if (isset($_FILES[$name]) && isset($_FILES[$name][$key]) && is_int($_FILES[$name][$key]))
    {
        $val = (int)intval($_FILES[$name][$key]);
    }

    return $val;
}

/**
 * @param string|mixed[] $target
 * @param string $key
 * @return bool
 */
function has_target_var($target, $key)
{
    if ($target === 'get')
    {
        return (isset($_GET[$key]) && is_string($_GET[$key]));
    }
    else if ($target === 'post')
    {
        return (isset($_POST[$key]) && is_string($_POST[$key]));
    }
    else if (is_array($target))
    {
        return isset($target[$key]);
    }

    return false;
}

/**
 * @param string|mixed[] $target
 * @param string $key
 * @param string $default
 * @param int $max_length
 * @return string
 */
function get_target_string($target, $key, $default = '', $max_length = 0)
{
    $val = $default;

    if ($target === 'get')
    {
        if (isset($_GET[$key]))
        {
            $val = utf8_sanitize((string)$_GET[$key]);
        }
    }
    else if ($target === 'post')
    {
        if (isset($_POST[$key]))
        {
            $val = utf8_sanitize((string)$_POST[$key]);
        }
    }
    else if (is_array($target))
    {
        if (isset($target[$key]))
        {
            $val = utf8_sanitize((string)$target[$key]);
        }
    }

    if ($max_length > 0)
    {
        $val = mb_substr($val, 0, $max_length);
    }

    return $val;
}

/**
 * @param string|mixed[] $target
 * @param string $key
 * @param int $default
 * @return int
 */
function get_target_int($target, $key, $default = 0)
{
    if (!has_target_var($target, $key))
    {
        return $default;
    }

    $intstr = get_target_string($target, $key, '0', 64);
    return (int)intval($intstr);
}

/**
 * @param string|mixed[] $target
 * @param string $key
 * @param float $default
 * @return float
 */
function get_target_float($target, $key, $default = 0.0)
{
    if (!has_target_var($target, $key))
    {
        return $default;
    }

    $floatstr = get_target_string($target, $key, '0.0', 64);
    return (float)floatval($floatstr);
}
