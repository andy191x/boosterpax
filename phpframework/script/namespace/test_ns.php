<?php

//
// Namespacing
//

//use \Manastock\Datamodel\Address;

//
// Includes
//

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../phpframework/utility.php');
require_once(__DIR__ . '/address.php');
include(__DIR__ . '/inc.php');

//
// Script logic
//

exit(main($argv));

//
// Global routines
//

function main($argv)
{
    // Configure PHP
    unicode_configure_php_utf8();

    // Run tests
    $address = new Address();


    return 0;
}