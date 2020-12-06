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
    unicode_configure_php_utf8();

    /*
    $val = utf8_sanitize("andy\xff\xfe");
    var_dump($val);
    var_dump(strlen($val));
    var_dump(mb_strlen($val));
    */

    $example_array = array(
        'Valid ASCII' => "a",
        'Valid 2 Octet Sequence' => "\xc3\xb1",
        'Invalid 2 Octet Sequence' => "\xc3\x28",
        'Invalid Sequence Identifier' => "\xa0\xa1",
        'Valid 3 Octet Sequence' => "\xe2\x82\xa1",
        'Invalid 3 Octet Sequence (in 2nd Octet)' => "\xe2\x28\xa1",
        'Invalid 3 Octet Sequence (in 3rd Octet)' => "\xe2\x82\x28",
        'Valid 4 Octet Sequence' => "\xf0\x90\x8c\xbc",
        'Invalid 4 Octet Sequence (in 2nd Octet)' => "\xf0\x28\x8c\xbc",
        'Invalid 4 Octet Sequence (in 3rd Octet)' => "\xf0\x90\x28\xbc",
        'Invalid 4 Octet Sequence (in 4th Octet)' => "\xf0\x28\x8c\x28",
        'Valid 5 Octet Sequence (but not Unicode!)' => "\xf8\xa1\xa1\xa1\xa1",
        'Valid 6 Octet Sequence (but not Unicode!)' => "\xfc\xa1\xa1\xa1\xa1\xa1",
    );

    foreach ($example_array as $test => $val)
    {
        $val = utf8_sanitize($val);
        echo $test . ' "' . $val . '" ' . strlen($val) . ' ' . mb_strlen($val) . PHP_EOL;

        if (strlen($val) > 0)
        {
            for ($i = 0; $i < strlen($val); $i++)
            {
                echo dechex(ord($val[$i])) . ' ';
            }
            echo PHP_EOL;
        }

        echo PHP_EOL;
    }

    return 0;
}