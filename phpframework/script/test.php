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
    var_dump(mb_trim('    asdf    '));
    var_dump(mb_ltrim('    asdf    '));
    var_dump(mb_rtrim('    asdf    '));

    var_dump(mb_trim('    asdf    ', ' '));
    var_dump(mb_ltrim('    asdf    ', ' '));
    var_dump(mb_rtrim('    asdf    ', ' '));
    */

    /*
    var_dump(mb_trim("\xc3\xb1\xc3\xb1asdf\xc3\xb1\xc3\xb1", "\xc3\xb1"));
    var_dump(mb_ltrim("\xc3\xb1\xc3\xb1asdf\xc3\xb1\xc3\xb1", "\xc3\xb1"));
    var_dump(mb_rtrim("\xc3\xb1\xc3\xb1asdf\xc3\xb1\xc3\xb1", "\xc3\xb1"));
    */

    /*
    var_dump(join_path('left//////////', '////////////right', '/'));
    var_dump(join_path('/left/', '/right/', '/'));
    var_dump(join_path('/left', 'right/', '/'));
    var_dump(join_path('/left/', 'right/', '/'));
    var_dump(join_path('/left/', '/right/', '/'));
    */

    /*
    $map = new Map();
    var_dump($map);
    */

    /*
    for ($i = 0; $i < 10; $i++)
    {
        $start = microtime();
        $int = random_int(0, PHP_INT_MAX);
        $end = microtime();

        echo $int . '(' . sprintf('%f', $end - $start) . ')' . PHP_EOL;
    }
    */

    return 0;
}