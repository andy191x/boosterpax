<?php

//
// Includes
//

require_once(__DIR__ . '/map.php');
require_once(__DIR__ . '/pdodbgateway.php');

//
// Types
//

class ORMObject extends Map
{
    //
    // Private data
    //
    
    /** @var PDODBWrapper|null */ private $db;
    /** @var string */ private $db_table;
    
    //
    // Public routines
    //

    /**
     * ORMObject constructor.
     * @param mixed[] $data_map
     */
    public function __construct($data_map = array())
    {
        parent::__construct();

        $this->db = null;
        $this->db_table = '';

        // Set empty val
        $emptyval = '';
        $this->setEmptyVal($emptyval);

        // Allocate array
        $fieldinfo = $this->getFieldInfo();

        foreach ($fieldinfo as $field)
        {
            if (isset($data_map[$field]))
            {
                parent::set($field, $data_map[$field]);
            }
            else
            {
                parent::set($field, $emptyval);
            }
        }
    }

    //
    // Overloadable routines
    //

    /**
     * Returns the default field info
     * @return string[] - array of field names.
     */
    public function getFieldInfo()
    {
        return array();
    }

    /**
     * Returns the primary key (if applicable)
     * @return string[] - array of field names for the primary key.
     */
    public function getPrimaryKey()
    {
        return array();
    }

    //
    // Database link routines
    //

    /**
     * @param PDODBWrapper|null $db
     * @param string $table
     */
    public function setDB($db, $table)
    {
        $this->db = $db;
        $this->db_table = $table;
    }

    /**
     * @return PDODBWrapper|null
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * @return bool
     */
    public function hasDB()
    {
        return (bool)($this->db !== null);
    }

    /**
     * @return string
     */
    public function getDBTable()
    {
        return $this->db_table;
    }

    /**
     *
     */
    public function clearDB()
    {
        $this->db = null;
        $this->db_table = '';
    }

    //
    // Inherited from Map
    //

    /**
     *
     */
    public function clear()
    {
        // NOOP
    }

    /**
     * @param mixed[] $map
     * @return bool
     */
    public function setMap($map)
    {
        // NOOP
        return false;
    }

    /**
     * @return mixed[]
     */
    public function getMap()
    {
        return parent::getMap();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @param string $key
     * @param mixed $val
     * @return bool
     */
    public function set($key, $val)
    {
        return self::setMany(array($key => $val));
    }

    /**
     * @param mixed[] $map
     * @return bool
     */
    public function setMany($map)
    {
        // Remove any invalid fields
        $has_invalid = false;
        $fieldinfo = $this->getFieldInfo();

        foreach ($map as $key => $val)
        {
            if (!in_array($key, $fieldinfo))
            {
                $has_invalid = true;
                break;
            }
        }

        if ($has_invalid)
        {
            $newmap = array();

            foreach ($map as $key => $val)
            {
                if (in_array($key, $fieldinfo))
                {
                    $newmap[$key] = $val;
                }
            }

            $map = $newmap;
        }

        // Check count
        if (count($map) == 0)
        {
            return true;
        }

        // Update
        if ($this->db !== null)
        {
            if (!$this->updateDB($map))
            {
                return false;
            }
        }

        return parent::setMany($map);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return parent::has($key);
    }

    //
    // Public static routines
    //

    /**
     * @param ORMObject[] $object_array
     * @return mixed[]
     */
    static public function arrayGetMap($object_array)
    {
        $array = array();

        foreach ($object_array as $object)
        {
            $array[] = $object->getMap();
        }

        return $array;
    }

    /**
     * @param ORMObject[] $object_map
     * @return mixed[]
     */
    static public function mapGetMap($object_map)
    {
        $map = array();

        foreach ($object_map as $key => $object)
        {
            $map[$key] = $object->getMap();
        }

        return $map;
    }

    //
    // Private routines
    //

    /**
     * @param mixed[] $map
     * @return bool
     */
    private function updateDB($map)
    {
        if (strlen($this->db_table) == 0)
        {
            return false;
        }

        $pk = $this->getPrimaryKey();
        if (count($pk) == 0)
        {
            return false;
        }

        $data_map = $map;
        foreach ($pk as $field)
        {
            $data_map[$field] = parent::get($field);
        }

        $error = null;
        $ok = PDODBGateway::dbUpdateByPK($this->db, $this->db_table, $pk, $data_map, $error);

        return $ok;
    }

    // ...
}