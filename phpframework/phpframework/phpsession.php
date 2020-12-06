<?php

//
// Includes
//

require_once(__DIR__ . '/errormap.php');

//
// Types
//

class PHPSession extends ErrorMap
{
    //
    // Private data
    //

    /** @var bool */ private $is_connected;
    /** @var string */ private $cookie_name;
    /** @var string */ private $cookie_domain;
    
    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();
        $this->is_connected = false;
        $this->cookie_name = '';
        $this->cookie_domain = '';
    }

    //
    // Overloadable routines
    //

    /**
     * Returns whether or not a field is writeable
     * @param string $field
     * @return bool
     */
    public function isFieldWriteable($field)
    {
        return true;
    }
    
    //
    // Public connection routines
    //

    /**
     * @param string $cookie_name
     * @return bool
     */
    public function setCookieName($cookie_name)
    {
        if ($this->is_connected)
        {
            return false;
        }

        $this->cookie_name = $cookie_name;
        return true;
    }

    /**
     * @param string $cookie_domain
     * @return bool
     */
    public function setCookieDomain($cookie_domain)
    {
        if ($this->is_connected)
        {
            return false;
        }

        $this->cookie_domain = $cookie_domain;
        return true;
    }

    /**
     * Connects to the default session for the current client. This can be a new session, or an existing session that was resumed.
     * @return bool
     */
    public function connectToDefault()
    {
        if ($this->is_connected)
        {
            return true;
        }

        if (strlen($this->cookie_name) > 0)
        {
            session_name($this->cookie_name);
        }

        if (strlen($this->cookie_domain) > 0)
        {
            ini_set('session.cookie_domain', $this->cookie_domain);
        }

        if (!session_start())
        {
            $this->addError(ErrorType::make(0, 'Cannot start session.'));
            return false;
        }

        $this->is_connected = true;
        return true;
    }

    /**
     * Connects to a specific session. Use getId to get the current session.
     * @param string $id
     * @return bool
     */
    public function connectToId($id)
    {
        if ($this->is_connected)
        {
            return true;
        }

        if (strlen($id) == 0)
        {
            $this->addError(ErrorType::make(0, 'Invalid session id.'));
            return false;
        }

        $curr_id = (string)session_id($id);

        if ($id !== $curr_id)
        {
            $this->addError(ErrorType::make(0, 'Cannot bind id.'));
            return false;
        }

        if (strlen($this->cookie_name) > 0)
        {
            session_name($this->cookie_name);
        }

        if (strlen($this->cookie_domain) > 0)
        {
            ini_set('session.cookie_domain', $this->cookie_domain);
        }

        if (!session_start())
        {
            $this->addError(ErrorType::make(0, 'Cannot start session.'));
            return false;
        }

        $this->is_connected = true;
        return true;
    }

    /**
     * Disconnects the current session and destroys it entirely.
     * This code was taken from the PHP manual.
     */
    public function disconnectAndDestroy()
    {
        if (!$this->is_connected)
        {
            return;
        }

        $this->is_connected = false;

        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }

    /**
     * Regenerates the session id. This should be performed after login.
     * @return bool
     */
    public function regenerateId()
    {
        if (!$this->is_connected)
        {
            return false;
        }

        return session_regenerate_id(true);
    }

    /**
     * Returns the current session id
     * @param string $out_id
     * @return bool
     */
    public function getId(&$out_id)
    {
        $out_id = '';

        if (!$this->is_connected)
        {
            return false;
        }

        $out_id = (string)session_id();
        if (strlen($out_id) == 0)
        {
            return false;
        }

        return true;
    }

    public function isConnected()
    {
        return $this->is_connected;
    }

    //
    // Inherited from Map
    //

    /**
     *
     */
    public function clear()
    {
        if (!$this->is_connected)
        {
            return;
        }

        $_SESSION = array();
    }

    /**
     * @param mixed[] $map
     * @return bool
     */
    public function setMap($map)
    {
        if (!$this->is_connected)
        {
            return false;
        }

        $_SESSION = array();
        return self::setMany($map);
    }

    /**
     * @return mixed[]
     */
    public function getMap()
    {
        if (!$this->is_connected)
        {
            return array();
        }

        return $_SESSION;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->is_connected)
        {
            return parent::getEmptyVal();
        }

        $val = isset($_SESSION[$key]) ? $_SESSION[$key] : parent::getEmptyVal();
        $strong_type = parent::getStrongType($key);

        if (strlen($strong_type) > 0)
        {
            $val = cast($val, $strong_type);
        }

        return $val;
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
        if (!$this->is_connected)
        {
            return false;
        }

        $ok = true;

        foreach ($map as $key => $val)
        {
            if (!$this->isFieldWriteable($key))
            {
                $ok = false;
                continue;
            }

            $strong_type = $this->getStrongType($key);
            if (strlen($strong_type) > 0)
            {
                $_SESSION[$key] = cast($val, $strong_type);
            }
            else
            {
                $_SESSION[$key] = $val;
            }
        }

        if (!$ok)
        {
            $this->addError(ErrorType::make(0, 'Attempted to write to one or more unwritable fields.'));
        }

        return $ok;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        if (!$this->is_connected)
        {
            return false;
        }

        return isset($_SESSION[$key]);
    }

    // ...
}