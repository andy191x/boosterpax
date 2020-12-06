<?php

//
// Includes
//

require_once(__DIR__ . '/errorbase.php');

//
// Types
//

class ErrorMap extends Map
{
	//
	// Private data
	//

	/** @var ErrorBase */ private $errorbase;

	//
	// Public routines
	//

	public function __construct()
	{
		parent::__construct();
		$this->errorbase = new ErrorBase();
	}

	//
	// Public ErrorBase pass-through routines
	//

	/**
	 * @param ErrorType|null $error
	 */
	public function addError($error)
	{
		$this->errorbase->addError($error);
	}

	public function clearError()
	{
		$this->errorbase->clearError();
	}

	public function getErrorCount()
	{
		return $this->errorbase->getErrorCount();
	}

	/**
	 * @return ErrorType
	 */
	public function getLastError()
	{
		return $this->errorbase->getLastError();
	}

	/**
	 * @return ErrorType
	 */
	public function popLastError()
	{
		return $this->errorbase->popLastError();
	}

	/**
	 * @param int $val
	 */
	public function setMaxErrorCount($val)
	{
		$this->errorbase->setMaxErrorCount($val);
	}

	/**
	 * @param string $delimiter
	 * @return string
	 */
	public function formatError($delimiter = "\n")
	{
		return $this->errorbase->formatError($delimiter);
	}

	// ...
}
