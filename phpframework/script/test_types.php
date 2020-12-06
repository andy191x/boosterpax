<?php

//
// Includes
//

require_once(__DIR__ . '/../autoload.php');

//
// Script logic
//

exit(main($argv));

//
// Global routines
//

function main($argv)
{
    Utility::load();
    var_dump(float_int(-100.00));
    var_dump(float_int(-2.00));
    var_dump(float_int(-1.99));
    var_dump(float_int(-1.51));
    var_dump(float_int(-1.50));
    var_dump(float_int(-1.49));
    var_dump(float_int(-1.01));
    var_dump(float_int(-1.00));
    var_dump(float_int(-0.01));
    var_dump(float_int(0.00));
    var_dump(float_int(0.01));
    var_dump(float_int(1.00));
    var_dump(float_int(1.01));
    var_dump(float_int(1.49));
    var_dump(float_int(1.50));
    var_dump(float_int(1.51));
    var_dump(float_int(1.99));
    var_dump(float_int(2.00));
    var_dump(float_int(100.00));

    var_dump(float_equal(1.0, 1.0, 0.01));
    var_dump(float_equal(1.01, 1.01, 0.001));
    var_dump(float_equal(1.001, 1.001, 0.001));
    var_dump(float_equal(1.001, 1.002, 0.001));
    var_dump(float_equal(1.001, 1.002, 0.01));

    return 0;
}