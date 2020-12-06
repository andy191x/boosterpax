<?php

//
// Includes
//

require_once(__DIR__ . '/errorbase.php');


//
// Types
//

class PDODBWrapper extends ErrorBase
{
    //
    // Private data
    //

    /** @var \PDO */                           private $pdo;
    /** @var \PDODBWrapperCallbackIntf[] */    private $callback_array;

    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();

        $this->pdo = null;
        $this->callback_array = array();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    //
    // Public error handling routines
    //

    /**
     * @param \PDODBWrapperCallbackIntf $callback
     */
    public function addCallback($callback)
    {
        $this->callback_array[] = $callback;
    }

    //
    // Public connection routines
    //

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $dbname
     * @param string $charset
     * @return bool
     */
    public function connect($host, $user, $password, $dbname, $charset = 'utf8mb4')
    {
        $this->disconnect();

        try
        {
            $options = array();
            $options[PDO::ATTR_PERSISTENT] = false;
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

            $dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;

            $this->pdo = new PDO($dsn, $user, $password, $options);

            return true;
        }
        catch (PDOException $e)
        {
            $this->pdo = null;
            $this->addError(ErrorType::makeByText('Connection failed: ' . $e->getMessage()));

            return false;
        }
    }

    /**
     *
     */
    public function disconnect()
    {
        if ($this->pdo === null)
        {
            return;
        }

        $this->pdo = null;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return (bool)($this->pdo !== null);
    }

    //
    // Public DB operations
    //

    /**
     * Standard unchecked SQL query execution.
     * @param string $sql
     * @param string[]|null $out_row_array
     * @param int|null $out_rows_affected
     * @return bool
     */
    public function execute($sql, &$out_row_array = null, &$out_rows_affected = null)
    {
        if (!is_null($out_row_array))
        {
            $out_row_array = array();
        }

        if (!is_null($out_rows_affected))
        {
            $out_rows_affected = 0;
        }

        if (!$this->isConnected())
        {
            $this->addError(ErrorType::makeByText('Operation performed without a database connection.'));
            return false;
        }

        if (count($this->callback_array) > 0)
        {
            foreach ($this->callback_array as $callback)
            {
                $callback->onPDODBWrapperBeforeExecute($sql, array());
            }
        }

        try
        {
            $pdo_statement = $this->pdo->query($sql);

            if (!is_null($out_row_array))
            {
                $out_row_array = $pdo_statement->fetchAll(PDO::FETCH_ASSOC);
            }

            if (!is_null($out_rows_affected))
            {
                $out_rows_affected = $pdo_statement->rowCount();
            }

            return true;
        }
        catch (PDOException $e)
        {
            $this->addError(ErrorType::makeByText('Query failed: ' . $e->getMessage()));
            return false;
        }
    }

    /**
     * Prepared SQL query execution.
     * The SQL statement can contain zero or more named (:name) or question mark (?) parameter markers for which real values will be substituted when the statement is executed.
     * See PDO prepare for more details: http://www.php.net/manual/en/pdo.prepare.php
     * @param string $sql
     * @param mixed[] $arg_array
     * @param string[]|null $out_row_array
     * @param int|null $out_rows_affected
     * @return bool
     */
    public function pexecute($sql, $arg_array, &$out_row_array = null, &$out_rows_affected = null)
    {
        if (!is_null($out_row_array))
        {
            $out_row_array = array();
        }

        if (!is_null($out_rows_affected))
        {
            $out_rows_affected = 0;
        }

        if (!$this->isConnected())
        {
            $this->addError(ErrorType::makeByText('Operation performed without a database connection.'));
            return false;
        }

        if (count($this->callback_array) > 0)
        {
            foreach ($this->callback_array as $callback)
            {
                $callback->onPDODBWrapperBeforeExecute($sql, $arg_array);
            }
        }

        try
        {
            $pdo_statement = $this->pdo->prepare($sql);
            $pdo_statement->execute($arg_array);

            if (!is_null($out_row_array))
            {
                $out_row_array = $pdo_statement->fetchAll(PDO::FETCH_ASSOC);
            }

            if (!is_null($out_rows_affected))
            {
                $out_rows_affected = $pdo_statement->rowCount();
            }

            return true;
        }
        catch (PDOException $e)
        {
            $this->addError(ErrorType::makeByText('Query failed: ' . $e->getMessage()));
            return false;
        }
    }

    /**
     * @param string $col_name
     * @param mixed $out_insert_id
     * @return bool
     */
    public function getLastInsertId($col_name, &$out_insert_id)
    {
        $out_insert_id = 0;

        if (!$this->isConnected())
        {
            $this->addError(ErrorType::makeByText('Operation performed without a database connection.'));
            return false;
        }

        try
        {
            $out_insert_id = $this->pdo->lastInsertId($col_name);
            return true;
        }
        catch (PDOException $e)
        {
            $this->addError(ErrorType::makeByText('Insert ID lookup failed: ' . $e->getMessage()));
            return false;
        }
    }

    /**
     * @return bool
     */
    public function transactionStart()
    {
        return $this->execute('start transaction');
    }

    /**
     * @return bool
     */
    public function transactionCommit()
    {
        return $this->execute('commit');
    }

    /**
     * @return bool
     */
    public function transactionRollback()
    {
        return $this->execute('rollback');
    }

    /**
     * @param string $lock_name
     * @param int $timeout_secs
     * @return bool
     */
    public function mutexLock($lock_name, $timeout_secs)
    {
        $row_array = array();

        if ($this->pexecute('select GET_LOCK(?, ?) as "lock"', array($lock_name, $timeout_secs), $row_array))
        {
            if ((count($row_array) == 1) && isset($row_array[0]['lock']))
            {
                $val = (int)$row_array[0]['lock'];

                if ($val == 1)
                {
                    return true;
                }
                else
                {
                    $this->addError(ErrorType::makeByText('Lock acquired by another session.'));
                }
            }
            else
            {
                $this->addError(ErrorType::makeByText('Cannot parse row structure: ' . print_r($row_array, true)));
            }
        }
        else
        {
            // Relay error from pexecute()
            // NOOP
        }

        return false;
    }

    /**
     * @param $lock_name
     * @return bool
     */
    public function mutexUnlock($lock_name)
    {
        if ($this->pexecute('select RELEASE_LOCK(?)', array($lock_name)))
        {
            return true;
        }
        else
        {
            // Relay error from pexecute()
            // NOOP
        }

        return false;
    }

    /**
     * Gets the current local time from the database server as a DATETIME string
     * @param string $out_val
     * @return bool
     */
    public function getLocalDATETIMEAsString(&$out_val)
    {
        $out_val = '';
        $row_array = array();

        if ($this->execute('select NOW() as now', $row_array))
        {
            if (count($row_array) == 1)
            {
                $out_val = $row_array[0]['now'];
                return true;
            }
            else
            {
                $this->addError(ErrorType::makeByText('Time lookup returned zero rows.'));
            }
        }
        else
        {
            // Relay error from execute()
            // NOOP
        }

        return false;
    }

    /**
     * Gets the current local time from the database server as a DateTime object
     * @param \DateTime|null $out_val
     * @return bool
     */
    public function getLocalDATETIMEAsDT(&$out_val)
    {
        $out_val = null;
        $row_array = array();

        if ($this->execute('select NOW() as now', $row_array))
        {
            if (count($row_array) == 1)
            {
                try
                {
                    $out_val = new DateTime($row_array[0]['now']);
                    return true;
                }
                catch (Exception $e)
                {
                    $this->addError(ErrorType::makeByText('Unable to format the database time.'));
                }
            }
            else
            {
                $this->addError(ErrorType::makeByText('Time lookup returned zero rows.'));
            }
        }
        else
        {
            // Relay error from execute()
            // NOOP
        }

        return false;
    }

    /**
     * Gets the current local time in UTC from the database server as a DATETIME string
     * @param string $out_val
     * @return bool
     */
    public function getLocalDATETIMEUTCAsString(&$out_val)
    {
        $out_val = '';
        $row_array = array();

        if ($this->execute('select UTC_TIMESTAMP() as now', $row_array))
        {
            if (count($row_array) == 1)
            {
                $out_val = $row_array[0]['now'];
                return true;
            }
            else
            {
                $this->addError(ErrorType::makeByText('Time lookup returned zero rows.'));
            }
        }
        else
        {
            // Relay error from execute()
            // NOOP
        }

        return false;
    }

    /**
     * Gets the current local time in UTC from the database server as a DateTime object
     * @param \DateTime|null $out_val
     * @return bool
     */
    public function getLocalDATETIMEUTCAsDT(&$out_val)
    {
        $out_val = null;
        $row_array = array();

        if ($this->execute('select UTC_TIMESTAMP() as now', $row_array))
        {
            if (count($row_array) == 1)
            {
                try
                {
                    $out_val = new DateTime($row_array[0]['now']);
                    return true;
                }
                catch (Exception $e)
                {
                    $this->addError(ErrorType::makeByText('Unable to format the database time.'));
                }
            }
            else
            {
                $this->addError(ErrorType::makeByText('Time lookup returned zero rows.'));
            }
        }
        else
        {
            // Relay error from execute()
            // NOOP
        }

        return false;
    }

    /**
     * Makes a string of pexecute arg placeholders. pexecuteArgString(2) = '?, ?'
     * @param string $count
     * @return string
     */
    public function pexecuteArgString($count)
    {
        $str = '';

        for ($i = 0; $i < $count; $i++)
        {
            if ($i > 0)
            {
                $str .= ', ';
            }
            $str .= '?';
        }

        return $str;
    }

    //
    // Inherited from ErrorBase
    //

    /**
     * @param ErrorType $error
     */
    public function addError($error)
    {
        parent::addError($error);

        if (count($this->callback_array) > 0)
        {
            foreach ($this->callback_array as $callback)
            {
                $callback->onPDODBWrapperError($error);
            }
        }
    }

    // ...
}
