<?php

//
// Includes
//

require_once(__DIR__ . '/errorbase.php');

//
// Types
//

class PDODBGateway extends ErrorBase
{
    //
    // Private data
    //

    /** @var PDODBWrapper */ private $db;
    /** @var string */ private $db_table;
    /** @var string */ private $object_name;

    //
    // Public routines
    //

    /**
     * @param PDODBWrapper $pdodbwrapper
     * @param string $table
     * @param string $object_name
     */
    public function __construct(&$pdodbwrapper, $table, $object_name)
    {
        parent::__construct();

        $this->db = $pdodbwrapper;
        $this->db_table = $table;
        $this->object_name = $object_name;
    }

    //
    // Database link routines
    //

    /**
     * @param PDODBWrapper $db
     * @param string $table
     */
    public function setDB($db, $table)
    {
        $this->db = $db;
        $this->db_table = $table;
    }

    /**
     * @return PDODBWrapper
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getDBTable()
    {
        return $this->db_table;
    }

    //
    // Object link routines
    //

    /**
     * @param string $val
     */
    public function setObjectName($val)
    {
        $this->object_name = $val;
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return $this->object_name;
    }
    
    //
    // Object gateway routines
    //

    /**
     * @param mixed[] $data_map
     * @param bool $update_existing
     * @param bool $load_object
     * @param mixed $out_object
     * @return bool
     */
    protected function objInsert($data_map, $update_existing, $load_object, &$out_object)
    {
        $out_object = null;

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        $new_object = null;
        $error = null;
        if (!self::dbInsert($db, $object_name, $table, $pk, $data_map, $update_existing, $load_object, $new_object, $error))
        {
            $this->addError($error);
            return false;
        }

        $out_object = $new_object;
        return true;
    }

    /**
     * @param mixed[] $data_map
     * @param mixed $out_object
     * @return bool
     */
    protected function objInsertAI($data_map, &$out_object)
    {
        $out_object = null;

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        $new_object = null;
        $error = null;
        if (!self::dbInsertAI($db, $object_name, $table, $pk, $data_map, $new_object, $error))
        {
            $this->addError($error);
            return false;
        }

        $out_object = $new_object;
        return true;
    }

    /**
     * @param mixed[] $data_map
     * @param bool $update_existing
     * @param bool $load_object
     * @param int $pk_int_size
     * @param bool $pk_int_unsigned
     * @param int $pk_try_count
     * @param mixed $out_object
     * @return bool
     */
    protected function objInsertUnique($data_map, $update_existing, $load_object, $pk_int_size, $pk_int_unsigned, $pk_try_count, &$out_object)
    {
        $out_object = null;

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        // Make unique PK
        $new_pk = array();
        if (!self::dbUniquePKInt($db, $table, $pk, $pk_int_size, $pk_int_unsigned, $pk_try_count, $new_pk, $error))
        {
            $this->addError($error);
            return false;
        }

        // Join PK with supplied data
        $data_map_edit = $data_map;
        foreach ($new_pk as $key => $val)
        {
            $data_map_edit[$key] = $val;
        }

        // Insert
        $new_object = null;
        $error = null;
        if (!self::dbInsert($db, $object_name, $table, $pk, $data_map_edit, $update_existing, $load_object, $new_object, $error))
        {
            $this->addError($error);
            return false;
        }

        $out_object = $new_object;
        return true;
    }

    /**
     * @param mixed[] $data_map
     * @param bool $update_existing
     * @param bool $load_object
     * @param int $min
     * @param int $max
     * @param int $pk_try_count
     * @param mixed $out_object
     * @return bool
     */
    protected function objInsertUniqueRange($data_map, $update_existing, $load_object, $min, $max, $pk_try_count, &$out_object)
    {
        $out_object = null;

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        // Make unique PK
        $new_pk = array();
        if (!self::dbUniquePKRange($db, $table, $pk, $min, $max, $pk_try_count, $new_pk, $error))
        {
            $this->addError($error);
            return false;
        }

        // Join PK with supplied data
        $data_map_edit = $data_map;
        foreach ($new_pk as $key => $val)
        {
            $data_map_edit[$key] = $val;
        }

        // Insert
        $new_object = null;
        $error = null;
        if (!self::dbInsert($db, $object_name, $table, $pk, $data_map_edit, $update_existing, $load_object, $new_object, $error))
        {
            $this->addError($error);
            return false;
        }

        $out_object = $new_object;
        return true;
    }

    /**
     * @param mixed[] $pk_data_map
     * @param mixed $out_object
     * @return bool
     */
    protected function objFindOneByPK($pk_data_map, &$out_object)
    {
        $out_object = null;

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        $new_object = null;
        $error = null;
        if (!self::dbSelectOneByPK($db, $object_name, $table, $pk, $pk_data_map, $new_object, $error))
        {
            $this->addError($error);
            return false;
        }

        $out_object = $new_object;
        return true;
    }

    /**
     * Finds "NO EXACT MATCH" hit against a primary key. This is the proper way to test if a single record exists because database errors and
     * empty result sets won't be lumped up as a single boolean result.
     * @param mixed[] $pk_data_map
     * @return bool
     */
    protected function objFindNoMatchByPK($pk_data_map)
    {
        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        $new_object = null;
        $error = null;
        if (!self::dbSelectOneByPK($db, $object_name, $table, $pk, $pk_data_map, $new_object, $error))
        {
            if ($error->getCode() == ErrorCode::NO_EXACT_MATCH)
            {
                return true;
            }
            else
            {
                $this->addError($error);
            }
        }
        else
        {
            $this->addError(ErrorType::makeByText('Object exists.'));
        }

        return false;
    }

    /**
     * Finds "NO EXACT MATCH" hit against any field match. This is the proper way to test if a single record exists because database errors and
     * empty result sets won't be lumped up as a single boolean result.
     * @param mixed[] $data_map
     * @return bool
     */
    protected function objFindNoMatchByMatch($data_map)
    {
        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $new_object = null;
        $error = null;
        if (!self::dbSelectOneByMatch($db, $object_name, $table, $data_map, $new_object, $error))
        {
            if ($error->getCode() == ErrorCode::NO_EXACT_MATCH)
            {
                return true;
            }
            else
            {
                $this->addError($error);
            }
        }
        else
        {
            $this->addError(ErrorType::makeByText('Object exists.'));
        }

        return false;
    }

    /**
     * @param mixed[] $data_map
     * @param mixed $out_object
     * @return bool
     */
    protected function objFindOneByMatch($data_map, &$out_object)
    {
        $out_object = null;

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $new_object = null;
        $error = null;
        if (!self::dbSelectOneByMatch($db, $object_name, $table, $data_map, $new_object, $error))
        {
            $this->addError($error);
            return false;
        }

        $out_object = $new_object;
        return true;
    }

    /**
     * @param mixed[] $data_map - Indexed by column name
     * @param int $offset - 0-based index
     * @param int $max_count - 0 = infinite
     * @param mixed[] $out_object_array
     * @return bool
     */
    protected function objFindManyByMatch($data_map, $offset, $max_count, &$out_object_array)
    {
        $out_object_array = null;

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $new_object_array = array();
        $error = null;
        if (!self::dbSelectManyByMatch($db, $object_name, $table, $data_map, $offset, $max_count, $new_object_array, $error))
        {
            $this->addError($error);
            return false;
        }

        $out_object_array = $new_object_array;
        return true;
    }

    /**
     * @param mixed[] $pk_data_map
     * @return bool
     */
    protected function objDeleteByPK($pk_data_map)
    {
        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        $error = null;
        if (!self::dbDeleteByPK($db, $table, $pk, $pk_data_map, $error))
        {
            $this->addError($error);
            return false;
        }

        return true;
    }

    /**
     * @param int $int_size
     * @param bool $int_unsigned
     * @param int $try_count
     * @param string[] $out_pk - Indexed by column name
     * @return bool
     */
    protected function objUniquePKInt($int_size, $int_unsigned, $try_count, &$out_pk)
    {
        $out_pk = array();

        $db = $this->getDB();
        $table = $this->getDBTable();
        $object_name = $this->getObjectName();

        $object = new $object_name;
        $pk = $object->getPrimaryKey();

        $error = null;
        if (!self::dbUniquePKInt($db, $table, $pk, $int_size, $int_unsigned, $try_count, $out_pk, $error))
        {
            $this->addError($error);
            return false;
        }

        return true;
    }

    //
    // Static db gateway routines
    //

    /**
     * Updates specific fields in an existing row in a table by its primary key. Only columns not within the primary key can be changed.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $table_name
     * @param string[] $pk
     * @param mixed[] $data_map - Indexed by column name. Must contain primary key data.
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbUpdateByPK(&$pdodbwrapper, $table_name, $pk, $data_map, &$out_error)
    {
        $out_error = new ErrorType();

        // Check input
        if (count($data_map) == 0)
        {
            $out_error = ErrorType::makeByText('Data fields not defined.');
            return false;
        }

        if (count($pk) == 0)
        {
            $out_error = ErrorType::makeByText('PK fields not defined.');
            return false;
        }

        foreach ($pk as $column)
        {
            if (!isset($data_map[$column]))
            {
                $out_error = ErrorType::makeByText('Missing data for one or more PK fields.');
                return false;
            }
        }

        if (count($data_map) < (count($pk) + 1))
        {
            $out_error = ErrorType::makeByText('Not enough fields defined.');
            return false;
        }

        // Generate SQL
        $sql = '';
        $arg_array = array();

        $sql .= 'update ' . $table_name . ' set ';
        $data_index = 0;
        foreach ($data_map as $column => $value)
        {
            if (!in_array($column, $pk))
            {
                if ($data_index > 0)
                {
                    $sql .= ', ';
                }

                $sql .= $column . ' = ? ';
                $arg_array[] = (string)$value;

                $data_index++;
            }
        }

        $sql .= 'where ';

        $pk_index = 0;
        foreach ($pk as $column)
        {
            if ($pk_index > 0)
            {
                $sql .= 'and ';
            }

            $sql .= $column . ' = ? ';
            $arg_array[] = (string)$data_map[$column];

            $pk_index++;
        }

        $sql .= 'limit 1';

        // Run query
        if (!$pdodbwrapper->pexecute($sql, $arg_array))
        {
            $out_error = ErrorType::makeByText('Unable to update database: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        return true;
    }

    /**
     * Inserts a row into a table with an auto-increment primary key. An object is returned representing that row.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $object_name
     * @param string $table_name
     * @param string[] $pk
     * @param mixed[] $data_map - Indexed by column name
     * @param mixed|null $out_object
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbInsertAI(&$pdodbwrapper, $object_name, $table_name, $pk, $data_map, &$out_object, &$out_error)
    {
        $out_object = null;
        $out_error = new ErrorType();

        // Generate SQL for insert query
        $sql = '';
        $arg_array = array();

        $sql .= 'insert into ' . $table_name . ' ';
        $sql .= '( ';

        $data_index = 0;
        foreach ($data_map as $column => $value)
        {
            if ($data_index > 0)
            {
                $sql .= ', ';
            }

            $sql .= $column . ' ';
            $data_index++;
        }

        $sql .= ') ';
        $sql .= 'values ';
        $sql .= '( ';

        $data_index = 0;
        foreach ($data_map as $value)
        {
            if ($data_index > 0)
            {
                $sql .= ', ';
            }

            $sql .= '?';
            $arg_array[] = (string)$value;

            $data_index++;
        }

        $sql .= ') ';

        // Run query
        if (!$pdodbwrapper->pexecute($sql, $arg_array))
        {
            $out_error = ErrorType::makeByText('Unable to insert into database: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        // Get last insert ID
        $pk_insert = array();

        foreach ($pk as $column)
        {
            $insert_id = 0;
            $db_ok = $pdodbwrapper->getLastInsertId($column, $insert_id);

            if (!$db_ok)
            {
                $out_error = ErrorType::makeByText('Unable to retrieve insert ID for column: ' . $column . ' Error: ' . $pdodbwrapper->getLastError()->format());
                $pdodbwrapper->clearError();
                return false;
            }

            $pk_insert[$column] = $insert_id;
        }

        // Generate SQL for select query
        $sql = '';
        $arg_array = array();

        $sql .= 'select * from ' . $table_name . ' where ';

        $pk_index = 0;
        foreach ($pk as $column)
        {
            if ($pk_index > 0)
            {
                $sql .= 'and ';
            }

            $sql .= $column . ' = ? ';
            $arg_array[] = $pk_insert[$column];

            $pk_index++;
        }

        $sql .= 'limit 1';

        // Run query
        $row_array = array();

        if (!$pdodbwrapper->pexecute($sql, $arg_array, $row_array))
        {
            $out_error = ErrorType::makeByText('Unable to lookup newly inserted item: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        if (count($row_array) != 1)
        {
            $out_error = ErrorType::makeByText('No exact match on newly inserted item.');
            return false;
        }

        $out_object = new $object_name($row_array[0]);
        $out_object->setDB($pdodbwrapper, $table_name);

        return true;
    }

    /**
     * Inserts a row into a table without an auto-increment primary key. An object is returned representing that row. If the row already exists, it may be updated.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $object_name
     * @param string $table_name
     * @param string[] $pk
     * @param mixed[] $data_map - Indexed by column name
     * @param bool $update_existing
     * @param bool $load_object
     * @param mixed|null $out_object
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbInsert(&$pdodbwrapper, $object_name, $table_name, $pk, $data_map, $update_existing, $load_object, &$out_object, &$out_error)
    {
        $out_object = null;
        $out_error = new ErrorType();

        // Check input
        if (count($data_map) == 0)
        {
            $out_error = ErrorType::makeByText('Data fields not defined.');
            return false;
        }

        if (count($pk) == 0)
        {
            $out_error = ErrorType::makeByText('PK fields not defined.');
            return false;
        }

        foreach ($pk as $column)
        {
            if (!isset($data_map[$column]))
            {
                $out_error = ErrorType::makeByText('Missing data for one or more PK fields.');
                return false;
            }
        }

        if (count($data_map) < count($pk))
        {
            $out_error = ErrorType::makeByText('Not enough fields defined.');
            return false;
        }

        // Generate SQL for insert query
        $sql = '';
        $arg_array = array();

        $sql .= 'insert into ' . $table_name . ' ';
        $sql .= '( ';

        $data_index = 0;
        foreach ($data_map as $column => $value)
        {
            if ($data_index > 0)
            {
                $sql .= ', ';
            }

            $sql .= $column . ' ';
            $data_index++;
        }

        $sql .= ') ';
        $sql .= 'values ';
        $sql .= '( ';

        $data_index = 0;
        foreach ($data_map as $value)
        {
            if ($data_index > 0)
            {
                $sql .= ', ';
            }

            $sql .= '?';
            $arg_array[] = (string)$value;

            $data_index++;
        }

        $sql .= ') ';

        if ($update_existing)
        {
            $sql .= 'on duplicate key update ';

            $data_index = 0;
            foreach ($data_map as $column => $value)
            {
                if (!isset($pk[$column]))
                {
                    if ($data_index > 0)
                    {
                        $sql .= ', ';
                    }

                    $sql .= $column . '=VALUES(' . $column . ') ';
                    $data_index++;
                }
            }
        }

        // Run query
        if (!$pdodbwrapper->pexecute($sql, $arg_array))
        {
            $out_error = ErrorType::makeByText('Unable to insert/update database: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        // Lookup inserted/updated item
        if ($load_object)
        {
            $lookup_data_map = array();

            foreach ($pk as $column)
            {
                $lookup_data_map[$column] = (string)$data_map[$column];
            }

            $lookup_error = new ErrorType();
            $db_ok = self::dbSelectOneByPK($pdodbwrapper, $object_name, $table_name, $pk, $lookup_data_map, $out_object, $lookup_error);

            if (!$db_ok)
            {
                $out_error = ErrorType::makeByText('Unable to lookup inserted/updated item: ' . $lookup_error->format());
                return false;
            }
        }

        return true;
    }

    /**
     * Deletes a row from a table by its primary key.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $table_name
     * @param string[] $pk
     * @param mixed[] $data_map - Indexed by column name
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbDeleteByPK(&$pdodbwrapper, $table_name, $pk, $data_map, &$out_error)
    {
        $out_error = new ErrorType();

        // Check input
        if (count($data_map) == 0)
        {
            $out_error = ErrorType::makeByText('No fields defined.');
            return false;
        }

        if (count($data_map) != count($pk))
        {
            $out_error = ErrorType::makeByText('Not enough fields defined.');
            return false;
        }

        // Generate SQL for select query
        $sql = '';
        $arg_array = array();

        $sql .= 'delete from ' . $table_name . ' where ';

        $field_index = 0;
        foreach ($data_map as $column => $value)
        {
            if ($field_index > 0)
            {
                $sql .= 'and ';
            }

            $sql .= $column . ' = ? ';
            $arg_array[] = (string)$value;

            $field_index++;
        }

        $sql .= 'limit 1';

        // Run query
        if (!$pdodbwrapper->pexecute($sql, $arg_array))
        {
            $out_error = ErrorType::makeByText('Unable to delete from database: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        return true;
    }

    /**
     * Selects an object by its primary key. Only returns success if exactly one object is found.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $object_name
     * @param string $table_name
     * @param string[] $pk
     * @param mixed[] $data_map - Indexed by PK column name
     * @param mixed|null $out_object
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbSelectOneByPK(&$pdodbwrapper, $object_name, $table_name, $pk, $data_map, &$out_object, &$out_error)
    {
        $out_object = null;
        $out_error = new ErrorType();

        // Check input
        if (count($pk) == 0)
        {
            $out_error = ErrorType::makeByText('Invalid primary key.');
            return false;
        }

        if (count($data_map) != count($pk))
        {
            $out_error = ErrorType::makeByText('Invalid data.');
            return false;
        }

        $sql = 'select * from ' . $table_name . ' where ';
        $arg_array = array();

        $pk_index = 0;
        foreach ($pk as $column)
        {
            if ($pk_index > 0)
            {
                $sql .= 'and ';
            }

            $sql .= $column . ' = ? ';
            $arg_array[] = (string)$data_map[$column];

            $pk_index++;
        }

        $sql .= 'limit 2';

        $row_array = array();
        if (!$pdodbwrapper->pexecute($sql, $arg_array, $row_array))
        {
            $out_error = ErrorType::makeByText('Unable to perform lookup: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        if (count($row_array) != 1)
        {
            $out_error = ErrorType::make(ErrorCode::NO_EXACT_MATCH, 'No exact match.');
            return false;
        }

        $out_object = new $object_name($row_array[0]);
        $out_object->setDB($pdodbwrapper, $table_name);
        return true;
    }

    /**
     * Selects an object by one or more fields. Only returns success if exactly one object is found.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $object_name
     * @param string $table_name
     * @param mixed[] $match_data_map - Indexed by column name
     * @param mixed|null $out_object
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbSelectOneByMatch(&$pdodbwrapper, $object_name, $table_name, $match_data_map, &$out_object, &$out_error)
    {
        $out_object = null;
        $out_error = new ErrorType();

        // Check input
        if (count($match_data_map) == 0)
        {
            $out_error = ErrorType::makeByText('Invalid data.');
            return false;
        }

        $sql = 'select * from ' . $table_name . ' where ';
        $arg_array = array();

        $field_index = 0;
        foreach ($match_data_map as $column => $value)
        {
            if ($field_index > 0)
            {
                $sql .= 'and ';
            }

            $sql .= $column . ' = ? ';
            $arg_array[] = (string)$value;

            $field_index++;
        }

        $sql .= 'limit 2';

        $row_array = array();
        if (!$pdodbwrapper->pexecute($sql, $arg_array, $row_array))
        {
            $out_error = ErrorType::makeByText('Unable to perform lookup: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        if (count($row_array) != 1)
        {
            $out_error = ErrorType::make(ErrorCode::NO_EXACT_MATCH, 'No exact match.');
            return false;
        }

        $out_object = new $object_name($row_array[0]);
        $out_object->setDB($pdodbwrapper, $table_name);
        return true;
    }

    /**
     * Selects how many objects are in the database based on an exact match of one or more columns.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $table_name
     * @param mixed[] $match_data_map - Indexed by column name
     * @param int $out_count
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbSelectCountByMatch(&$pdodbwrapper, $table_name, $match_data_map, &$out_count, &$out_error)
    {
        $out_object = null;
        $out_error = new ErrorType();

        // Check input
        if (count($match_data_map) == 0)
        {
            $out_error = ErrorType::makeByText('Invalid data.');
            return false;
        }

        $sql = 'select count(*) as count from ' . $table_name . ' where ';
        $arg_array = array();

        $field_index = 0;
        foreach ($match_data_map as $column => $value)
        {
            if ($field_index > 0)
            {
                $sql .= 'and ';
            }

            $sql .= $column . ' = ? ';
            $arg_array[] = (string)$value;

            $field_index++;
        }

        $row_array = array();
        if (!$pdodbwrapper->pexecute($sql, $arg_array, $row_array))
        {
            $out_error = ErrorType::makeByText('Unable to perform lookup: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        if (count($row_array) != 1)
        {
            $out_error = ErrorType::makeByText('Invalid response schema.');
            return false;
        }

        $out_count = (int)$row_array[0]['count'];
        return true;
    }

    /**
     * Selects multiple objects based an exact match of one or more columns.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $object_name
     * @param string $table_name
     * @param mixed[] $match_data_map - Indexed by column name
     * @param int $offset - 0-based index
     * @param int $max_count - 0 = infinite
     * @param array|null $out_object_array
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbSelectManyByMatch(&$pdodbwrapper, $object_name, $table_name, $match_data_map, $offset, $max_count, &$out_object_array, &$out_error)
    {
        $out_object_array = array();
        $out_error = new ErrorType();

        // Check input
        if (count($match_data_map) == 0)
        {
            $out_error = ErrorType::makeByText('Invalid data.');
            return false;
        }

        $sql = 'select * from ' . $table_name . ' where ';
        $arg_array = array();

        $field_index = 0;
        foreach ($match_data_map as $column => $value)
        {
            if ($field_index > 0)
            {
                $sql .= 'and ';
            }

            $sql .= $column . ' = ? ';
            $arg_array[] = (string)$value;

            $field_index++;
        }

        $sql .= 'limit ' . (string)$offset . ',';
        if ($max_count == 0)
        {
            $sql .= '18446744073709551615 ';
        }
        else
        {
            $sql .= (string)$max_count . ' ';
        }

        $row_array = array();
        if (!$pdodbwrapper->pexecute($sql, $arg_array, $row_array))
        {
            $out_error = ErrorType::makeByText('Unable to perform lookup: ' . $pdodbwrapper->getLastError()->format());
            $pdodbwrapper->clearError();
            return false;
        }

        foreach ($row_array as $row)
        {
            $object = new $object_name($row);
            $object->setDB($pdodbwrapper, $table_name);
            $out_object_array[] = $object;
        }

        return true;
    }

    /**
     * Finds a unique integer primary key for tables without auto increment.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $table_name
     * @param string[] $pk
     * @param int $int_size - integer size in bits
     * @param bool $int_unsigned
     * @param int $try_count
     * @param string[] $out_pk
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbUniquePKInt(&$pdodbwrapper, $table_name, $pk, $int_size, $int_unsigned, $try_count, &$out_pk, &$out_error)
    {
        $out_pk = array();
        $out_error = new ErrorType();

        // Check input
        if ($try_count < 1)
        {
            $try_count = 1;
        }

        if (count($pk) == 0)
        {
            $out_error = ErrorType::makeByText('Invalid primary key.');
            return false;
        }

        // Determine int range
        // http://dev.mysql.com/doc/refman/5.6/en/integer-types.html
        $min = 1;
        $max = 2147483647;

        if (($int_size == 8) && (!$int_unsigned))
        {
            $max = 127;
        }
        else if (($int_size == 8) && ($int_unsigned))
        {
            $max = 255;
        }
        else if (($int_size == 16) && (!$int_unsigned))
        {
            $max = 32767;
        }
        else if (($int_size == 16) && ($int_unsigned))
        {
            $max = 65535;
        }
        else if (($int_size == 24) && (!$int_unsigned))
        {
            $max = 8388607;
        }
        else if (($int_size == 24) && ($int_unsigned))
        {
            $max = 16777215;
        }
        else if (($int_size == 32) && (!$int_unsigned))
        {
            $max = 2147483647;
        }
        else if (($int_size == 32) && ($int_unsigned))
        {
            $max = 4294967295;
        }
        else if (($int_size == 64) && (!$int_unsigned))
        {
            $max = 9223372036854775807;
        }
        else if (($int_size == 64) && ($int_unsigned))
        {
            $max = 9223372036854775807;

            // NOTE: actual range is larger than PHP_INT_MAX on 64-bit systems
            //$max = 18446744073709551615;
        }

        // Find open key
        return self::dbUniquePKRange($pdodbwrapper, $table_name, $pk, $min, $max, $try_count, $out_pk, $out_error);
    }

    /**
     * Finds a unique integer primary key (within a range) for tables without auto increment.
     * @param PDODBWrapper $pdodbwrapper
     * @param string $table_name
     * @param string[] $pk
     * @param int $min
     * @param int $max
     * @param int $try_count
     * @param string[] $out_pk
     * @param ErrorType $out_error
     * @return bool
     */
    static public function dbUniquePKRange(&$pdodbwrapper, $table_name, $pk, $min, $max, $try_count, &$out_pk, &$out_error)
    {
        $out_pk = array();
        $out_error = new ErrorType();

        // Check input
        if ($try_count < 1)
        {
            $try_count = 1;
        }

        if (count($pk) == 0)
        {
            $out_error = ErrorType::makeByText('Invalid primary key.');
            return false;
        }

        // Find open key
        for ($i = 0; $i < $try_count; $i++)
        {
            // Gen PK
            $new_pk = array();
            foreach ($pk as $column)
            {
                $new_pk[$column] = random_int($min, $max);
            }

            // Lookup
            $sql = 'select count(*) as count from ' . $table_name . ' where ';
            $arg_array = array();

            $field_index = 0;
            foreach ($new_pk as $column => $value)
            {
                if ($field_index > 0)
                {
                    $sql .= 'and ';
                }

                $sql .= $column . ' = ? ';
                $arg_array[] = (string)$value;

                $field_index++;
            }

            $row_array = array();
            if (!$pdodbwrapper->pexecute($sql, $arg_array, $row_array))
            {
                $out_error = ErrorType::makeByText('Unable to perform lookup' . ($i + 1) . ': ' . $pdodbwrapper->getLastError()->format());
                $pdodbwrapper->clearError();
                return false;
            }

            if (count($row_array) != 1)
            {
                $out_error = ErrorType::makeByText('Invalid response schema on lookup ' . ($i + 1) . '.');
                return false;
            }

            $count = (int)$row_array[0]['count'];

            // Check count
            if ($count == 0)
            {
                $out_pk = $new_pk;
                return true;
            }
        }

        $out_error = ErrorType::makeByText('Exhausted attempts without finding an open key.');
        return false;
    }

    // ...
}
