<?php

//
// Includes
//

require_once(__DIR__ . '/utility.types.php'); // for cast()

//
// Types
//

class Map
{
    //
    // Private data
    //

    /** @var mixed[] */ private $map;
    /** @var mixed[] */ private $strong_type_map;
    /** @var mixed */   private $empty_val;

    //
    // Public routines
    //

    public function __construct()
    {
        $this->map = array();
        $this->strong_type_map = array();
        $this->empty_val = '';
    }

    /**
     * @param mixed $val
     * @return bool
     */
    public function setEmptyVal($val)
    {
        $this->empty_val = $val;
        return true;
    }

    /**
     * @return mixed
     */
    public function getEmptyVal()
    {
        return $this->empty_val;
    }

    //
    // Public strong type routines
    //

    public function clearStrongType()
    {
        $this->strong_type_map = array();
    }

    public function setStrongType($key, $type)
    {
        $this->strong_type_map[$key] = $type;
    }

    public function getStrongType($key)
    {
        return isset($this->strong_type_map[$key]) ? $this->strong_type_map[$key] : '';
    }

    //
    // Public map routines
    //

    public function clear()
    {
        $this->map = array();
    }

    /**
     * Replaces the entire map contents with the given map.
     * @param mixed[] $map
     * @return bool
     */
    public function setMap($map)
    {
        $this->map = array();

        if (is_array($map))
        {
            $this->map = $map;
            return true;
        }

        return false;
    }

    /**
     * @return mixed[]
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $val = isset($this->map[$key]) ? $this->map[$key] : $this->empty_val;

        if (isset($this->strong_type_map[$key]))
        {
            $val = cast($val, $this->strong_type_map[$key]);
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
        $this->map[$key] = $val;

        if (isset($this->strong_type_map[$key]))
        {
            $this->map[$key] = cast($this->map[$key], $this->strong_type_map[$key]);
        }

        return true;
    }

    /**
     * @param mixed[] $map
     * @return bool
     */
    public function setMany($map)
    {
        foreach ($map as $key => $val)
        {
            if (isset($this->strong_type_map[$key]))
            {
                $this->map[$key] = cast($val, $this->strong_type_map[$key]);
            }
            else
            {
                $this->map[$key] = $val;
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->map[$key]);
    }

    //
    // Public getters/setters for types
    //

    public function getType($key, $type)
    {
        return cast($this->get($key), $type);
    }

    public function getString($key)
    {
        return (string)$this->get($key);
    }

    public function getInt($key)
    {
        return (int)$this->get($key);
    }

    public function getFloat($key)
    {
        return (float)$this->get($key);
    }

    public function getBool($key)
    {
        return (bool)$this->get($key);
    }

    public function setType($key, $val, $type)
    {
        return $this->set($key, cast($val, $type));
    }

    public function setString($key, $val)
    {
        return $this->set($key, (string)$val);
    }

    public function setInt($key, $val)
    {
        return $this->set($key, (int)$val);
    }

    public function setFloat($key, $val)
    {
        return $this->set($key, (float)$val);
    }

    public function setBool($key, $val)
    {
        return $this->set($key, (bool)$val);
    }

    // ...
}