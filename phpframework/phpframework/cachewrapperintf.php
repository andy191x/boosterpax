<?php

//
// Includes
//

// ...

//
// Types
//

interface CacheWrapperIntf
{
	//
	// Public routines
	//

	/**
	 * @param string $host
	 * @param int $port
	 * @param bool $persistent
	 * @return bool
	 */
	public function connect($host, $port, $persistent = true);

	/**
	 *
	 */
	public function disconnect();

	/**
	 * @return bool
	 */
	public function isConnected();

	/**
	 * @param string $key
	 * @param mixed $data
	 * @param int $expire_secs - 0 means infinite
	 * @return bool
	 */
	public function set($key, $data, $expire_secs = 0);

	/**
	 * @param string $key
	 * @param mixed|null $out_data
	 * @return bool
	 */
	public function get($key, &$out_data);

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key);

	/**
	 * @param string $key
	 * @return bool
	 */
	public function exists($key);

	/**
	 * @return bool
	 */
	public function flush();
}
