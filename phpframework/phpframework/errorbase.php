<?php

//
// Includes
//

require_once(__DIR__ . '/errortype.php');

//
// Types
//

class ErrorBase
{
	//
	// Private data
	//

	/** @var ErrorType[]  */    private $error_array;
	/** @var int */             private $max_error_count;

	//
	// Public routines
	//

	public function __construct()
	{
		$this->error_array = array();
		$this->max_error_count = 64;
	}

	/**
	 * @param ErrorType|null $error
	 */
	public function addError($error)
	{
		if (count($this->error_array) < $this->max_error_count)
		{
			if ($error === null)
			{
				$this->error_array[] = new ErrorType();
			}
			else
			{
				$this->error_array[] = $error;
			}
		}
	}

	public function clearError()
	{
		$this->error_array = array();
	}

	public function getErrorCount()
	{
		return count($this->error_array);
	}

	/**
	 * @return ErrorType
	 */
	public function getLastError()
	{
		$error_count = count($this->error_array);

		if ($error_count > 0)
		{
			return $this->error_array[$error_count - 1];
		}

		return new ErrorType();
	}

	/**
	 * @return ErrorType
	 */
	public function popLastError()
	{
		$error_count = count($this->error_array);

		if ($error_count > 0)
		{
			$error = $this->error_array[$error_count - 1];
			array_pop($this->error_array);
			return $error;
		}

		return new ErrorType();
	}

	/**
	 * @param int $val
	 */
	public function setMaxErrorCount($val)
	{
		$this->max_error_count = $val;
	}

	/**
	 * @param string $delimiter
	 * @return string
	 */
	public function formatError($delimiter = "\n")
	{
		$error = '';

		$error_count = count($this->error_array);

		if ($error_count > 0)
		{
			for ($i = 0; $i < $error_count; $i++)
			{
				if ($i > 0)
				{
					$error .= $delimiter;
				}

				$error .= $this->error_array[$i]->format();
			}
		}

		return $error;
	}

	// ...

}
