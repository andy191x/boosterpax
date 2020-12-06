<?php

//
// Includes
//

// ...

//
// Global routines
//

/**
 * Time string to unix timestamp with support for specifying the source timezone.
 * @param string $time
 * @param string $timezone
 * @param null|int $now
 * @return int
 */
function strtotimetz($time, $timezone, $now = null)
{
    $unix = 0;

    try
    {
        $datetime = new DateTime($time, new DateTimeZone($timezone));
        $unix = $datetime->getTimestamp();
    }
    catch (Exception $e)
    {
        // ...
    }

    return $unix;
}

/**
 * Unix timestamp to formatted time string with support for specifying the output timezone.
 * @param string $format
 * @param string $timezone
 * @param null|int $timestamp
 * @return bool|string
 */
function datetz($format, $timezone, $timestamp = null)
{
    $date = false;

    try
    {
        $datetime = new DateTime('now', new DateTimeZone($timezone));
        if ($timestamp !== null)
        {
            $datetime->setTimestamp($timestamp);
        }

        $date = $datetime->format($format);
    }
    catch (Exception $e)
    {
        // ...
    }

    return $date;
}