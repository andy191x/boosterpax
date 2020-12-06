<?php

//
// Includes
//

// ...

//
// Global routines
//

/**
 * @param mixed $map
 * @param mixed $key
 * @param mixed $default
 * @return mixed
 */
function isset_or_default($map, $key, $default)
{
    if (!is_array($map))
    {
        return $default;
    }

    return isset($map[$key]) ? $map[$key] : $default;
}

/**
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function defined_or_default($name, $default)
{
    return defined($name) ? constant($name) : $default;
}

/**
 * Cast a variable to another type by naming the type. This function follows PHP's type juggling rules.
 * @param mixed $val
 * @param string $type
 * @return mixed
 */
function cast($val, $type)
{
    settype($val, $type);
    return $val;
}

/**
 * Whether or not two floating point numbers are equal to a given precision.
 * @param float|double $a
 * @param float|double $b
 * @param float|double $epsilon
 * @return bool
 */
function float_equal($a, $b, $epsilon)
{
    return (abs($a - $b) <= $epsilon);
}

/**
 * Return the integer portion of a floating point number.
 * @param float|double $val
 * @return int
 */
function float_int($val)
{
    if ($val < 0.0)
    {
        return (int)round(ceil($val));
    }

    return (int)round(floor($val));
}

/**
 * @param mixed $min
 * @param mixed $max
 * @param mixed $val
 * @return mixed
 */
function clamp($min, $max, $val)
{
    if ($min > $max)
    {
        $x = $max;
        $max = $min;
        $min = $x;
    }

    if ($val < $min)
    {
        $val = $min;
    }

    if ($val > $max)
    {
        $val = $max;
    }

    return $val;
}

/**
 * Spaceship operator logic
 * @param mixed $a
 * @param mixed $b
 * @return int
 */
function spaceship($a, $b)
{
    if ($a < $b)
    {
        return -1;
    }

    if ($a > $b)
    {
        return 1;
    }

    return 0;
}