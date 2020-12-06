<?php

//
// Includes
//

// ...

//
// Types
//

class CacheWrapperRedis implements CacheWrapperIntf
{
	//
	// Private data
	//

	/** @var Redis */ private $redis;
	/** @var bool */ private $persistent;

	//
	// Public routines
	//

	public function __construct()
	{
		$this->redis = null;
		$this->persistent = false;
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	//
	// Inherited from CacheWrapperIntf
	//

	/**
	 * @param string $host
	 * @param int $port
	 * @param bool $persistent
	 * @return bool
	 */
	public function connect($host, $port, $persistent = true)
	{
		$this->disconnect();

		$this->redis = new Redis();
		$this->persistent = $persistent;

		$connected = false;

		try
		{
			if ($persistent)
			{
				$connected = $this->redis->pconnect($host, $port);
			}
			else
			{
				$connected = $this->redis->connect($host, $port);
			}
		}
		catch (Exception $e)
		{
			$connected = false;
		}

		if (!$connected)
		{
			$this->redis = null;
		}

		return $connected;
	}

	/**
	 *
	 */
	public function disconnect()
	{
		if ($this->redis !== null)
		{
			try
			{
				$this->redis->close();
			}
			catch (Exception $e)
			{
				// ...
			}

			$this->redis = null;
		}
	}

	/**
	 * @return bool
	 */
	public function isConnected()
	{
		return ($this->redis !== null);
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int $expire_secs - 0 means infinite
	 * @return bool
	 */
	public function set($key, $data, $expire_secs = 0)
	{
		if (!$this->isConnected())
		{
			return false;
		}

		$ok = false;

		try
		{
			$data_ser = serialize($data);

			if ($expire_secs <= 0)
			{
				$ok = $this->redis->set($key, $data_ser);
			}
			else
			{
				$ok = $this->redis->setex($key, $expire_secs, $data_ser);
			}
		}
		catch (Exception $e)
		{
			$ok = false;
		}

		return $ok;
	}

	/**
	 * @param string $key
	 * @param mixed|null $out_data
	 * @return bool
	 */
	public function get($key, &$out_data)
	{
		$out_data = null;

		if (!$this->isConnected())
		{
			return false;
		}

		$ok = false;

		try
		{
			$data_ser = $this->redis->get($key);

			if ($data_ser !== false)
			{
				$out_data = unserialize($data_ser);
				$ok = true;
			}
		}
		catch (Exception $e)
		{
			$ok = false;
		}

		return $ok;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key)
	{
		if (!$this->isConnected())
		{
			return false;
		}

		$ok = true;

		try
		{
			$this->redis->delete($key);
		}
		catch (Exception $e)
		{
			$ok = false;
		}

		return $ok;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function exists($key)
	{
		if (!$this->isConnected())
		{
			return false;
		}

		$exists = false;

		try
		{
			$exists = $this->redis->exists($key);
		}
		catch (Exception $e)
		{
			$exists = false;
		}

		return $exists;
	}

	/**
	 * @return bool
	 */
	public function flush()
	{
		if (!$this->isConnected())
		{
			return false;
		}

		return $this->redis->flushDB();
	}

	// ...
}
