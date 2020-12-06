<?php

//
// Namespacing
//

namespace Manastock\Datamodel;
use \ORMObject;

//
// Includes
//

require_once(__DIR__ . '/../../phpframework/ormobject.php');

//
// Types
//

class Address extends ORMObject
{
    //
    // Public routines
    //

    /**
     * @param mixed[] $data_map
     */
    public function __construct($data_map = array())
    {
        parent::__construct($data_map);
    }

    //
    // Overloadable routines
    //

    /**
     * @return string[]
     */
    public function getFieldInfo()
    {
        return array(
            'ADDRESSID',
            'line1',
            'line2',
            'line3',
            'city',
            'zipcode',
            'state',
            'notes'
        );
    }

    /**
     * @return string[]
     */
    public function getPrimaryKey()
    {
        return array(
            'ADDRESSID'
        );
    }

    //
    // Data format routines
    //

    // ...

    //
    // Data validate routines
    //

    // ...
}