<?php

//
// Includes
//

require_once(__DIR__ . '/../../phpframework/pdodbgateway.php');
require_once(__DIR__ . '/address.php');

//
// Types
//

class AddressGateway extends PDODBGateway
{
    //
    // Class constants
    //

    const TABLE_NAME = 'address';
    const OBJECT_NAME = 'Address';

    //
    // Public routines
    //

    /**
     * @param PDODBWrapper $pdodbwrapper
     * @param string $table
     * @param string $object_name
     */
    public function __construct(&$pdodbwrapper, $table = self::TABLE_NAME, $object_name = self::OBJECT_NAME)
    {
        parent::__construct($pdodbwrapper, $table, $object_name);
    }

    //
    // Table gateway routines
    //

    public function insert($data_map, &$out_object)
    {
        return $this->objInsertAI($data_map, $out_object);
    }

    public function findByPK($ADDRESSID, &$out_object)
    {
        $data_map = array();
        $data_map['ADDRESSID'] = $ADDRESSID;

        return $this->objFindOneByPK($data_map, $out_object);
    }

    public function deleteByPK($ADDRESSID)
    {
        $data_map = array();
        $data_map['ADDRESSID'] = $ADDRESSID;

        return $this->objDeleteByPK($data_map);
    }

    // ...
}