<?php

//
// Includes
//

// ...

//
// Global routines
//

/**
 * Configures the PHP internal character sets for UTF-8. Returns false if the configuration did not apply.
 * @return bool
 */
function unicode_configure_php_utf8()
{
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    mb_regex_encoding('UTF-8');
    mb_language('uni');

    mb_substitute_character('none');

    if (mb_internal_encoding() !== 'UTF-8')
    {
        return false;
    }
    if (mb_http_output() !== 'UTF-8')
    {
        return false;
    }
    if (mb_regex_encoding() !== 'UTF-8')
    {
        return false;
    }
    if (mb_language() !== 'uni')
    {
        return false;
    }

    return true;
}

/**
 * For rendering pages, this function will set the content-type charset to utf-8
 */
function unicode_set_contenttype_utf8($content_type = 'text/html')
{
    header('Content-Type: ' . $content_type . '; charset=utf-8');
}

/**
 * Checks encoding on a UTF-8 multibyte string. Invalid characters are stripped from the string.
 * @param string $val
 * @return string
 */
function utf8_sanitize($val)
{
    if (!mb_check_encoding($val, 'UTF-8'))
    {
        $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
    }

    return $val;
}

/**
 * Multibyte trim. Secure, but slow for custom character lists.
 * @param string $str
 * @param string|null $charlist
 * @return string
 */
function mb_trim($str, $charlist = null)
{
    if ($charlist === null)
    {
        return trim($str);
    }

    $edit = mb_ltrim($str, $charlist);
    $edit = mb_rtrim($edit, $charlist);

    return $edit;
}

/**
 * Multibyte trim. Secure, but slow for custom character lists.
 * @param string $str
 * @param string|null $charlist
 * @return string
 */
function mb_ltrim($str, $charlist = null)
{
    if ($charlist === null)
    {
        return ltrim($str);
    }

    $str_len = mb_strlen($str);
    $charlist_len = mb_strlen($charlist);

    $trim_count = 0;

    for ($i = 0; $i < $str_len; $i++)
    {
        $found = false;
        $str_char = mb_substr($str, $i, 1);

        for ($j = 0; $j < $charlist_len; $j++)
        {
            $list_char = mb_substr($charlist, $j, 1);

            if ($str_char == $list_char)
            {
                $found = true;
                break;
            }
        }

        if ($found)
        {
            $trim_count++;
        }
        else
        {
            break;
        }
    }

    if ($trim_count > 0)
    {
        return mb_substr($str, $trim_count);
    }

    return $str;
}

/**
 * Multibyte trim. Secure, but slow for custom character lists.
 * @param string $str
 * @param string|null $charlist
 * @return string
 */
function mb_rtrim($str, $charlist = null)
{
    if ($charlist === null)
    {
        return rtrim($str);
    }

    $str_len = mb_strlen($str);
    $charlist_len = mb_strlen($charlist);

    $trim_count = 0;

    for ($i = ($str_len - 1); $i >= 0; $i--)
    {
        $found = false;
        $str_char = mb_substr($str, $i, 1);

        for ($j = 0; $j < $charlist_len; $j++)
        {
            $list_char = mb_substr($charlist, $j, 1);

            if ($str_char == $list_char)
            {
                $found = true;
                break;
            }
        }

        if ($found)
        {
            $trim_count++;
        }
        else
        {
            break;
        }
    }

    if ($trim_count > 0)
    {
        return mb_substr($str, 0, ($str_len - $trim_count));
    }

    return $str;
}
