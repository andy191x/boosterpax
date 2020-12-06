<?php

//
// Includes
//

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../phpframework/utility.php');
require_once(__DIR__ . '/../../phpframework/pdodbwrapper.php');
require_once(__DIR__ . '/../../phpframework/pdodbgateway.php');
require_once(__DIR__ . '/../../phpframework/pdodbwrappercallbackintf.php');
require_once(__DIR__ . '/address.php');
require_once(__DIR__ . '/addressgateway.php');

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

    /*
    $address = new Address();
    //var_dump($address->set('ADDRESSID', '123'));
    //var_dump($address->setMany(array('ADDRESSID' => '123', 'line1' => '456', 'badkey' => 'badval')));
    //var_dump($address);

    //var_dump($address->get('ADDRESSID'));
    //var_dump($address->get('badkey'));

    var_dump($address->set('ADDRESSID', '123'));
    var_dump($address);
    var_dump($address->get('ADDRESSID'));
    */

    $pdodbwrapper = new PDODBWrapper();
    $ok = $pdodbwrapper->connect('127.0.0.1', 'manastock', 'abc', 'manastock');
    //var_dump($ok);
    //var_dump($pdodbwrapper->getLastError()->format());

    /*
    $row_array = array();
    $ok = $pdodbwrapper->execute('show variables like \'collation%\';', $row_array);
    var_dump($ok);
    var_dump($row_array);
    */

    //$addressgateway = new AddressGateway($pdodbwrapper);

    /*
    $data = array();
    $data['line1'] = 'mline1';
    $data['line2'] = 'mline2';
    $data['line3'] = 'mline3';
    $data['city'] = 'mcity';
    $data['zipcode'] = 'mzipcode';
    $data['state'] = 'mstate';
    $data['notes'] = 'mnotes';

    $address = null;
    $ok = $addressgateway->insert($data, $address);
    var_dump($ok);
    var_dump($address);
    */

    //$address = null;
    //$ok = $addressgateway->findByPK(3, $address);
    //var_dump($ok);
    //var_dump($address);

    //$ok = $address->set('line1', 'line1 edit2');
    //var_dump($ok);

    //$ok = $addressgateway->deleteByPK(3);
    //var_dump($ok);
    //var_dump($addressgateway->getLastError()->format());

    /*
    $new_pk = array();
    $error = null;
    $ok = PDODBGateway::dbUniquePKInt($pdodbwrapper, 'useraccount', array('USERACCOUNTID'), 32, true, 10, $new_pk, $error);
    var_dump($ok);
    var_dump($error->format());
    var_dump($new_pk);
    */

    return 0;
}