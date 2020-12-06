<?php

//
// Includes
//

require_once(__DIR__ . '/errorcode.php');

//
// Types
//

class ErrorType
{
	//
	// Private data
	//

	/** @var int */    private $code;
	/** @var string */ private $text;

	//
	// Public routines
	//

	public function __construct()
	{
		$this->code = ErrorCode::UNKNOWN;
		$this->text = 'An unknown error has occurred.';
	}

	/**
	 * @param int $val
	 */
	public function setCode($val)
	{
		$this->code = (int)$val;
	}

	/**
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $val
	 */
	public function setText($val)
	{
		$this->text = (string)$val;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @return string
	 */
	public function format()
	{
		return $this->code . ': ' . $this->text;
	}

	//
	// Cast routines
	//

	/**
	 * @param mixed $val
	 * @return ErrorType
	 */
	static public function cast($val)
	{
		return $val;
	}

	/**
	 * @param mixed $val
	 * @return ErrorType[]
	 */
	static public function castArray($val)
	{
		return $val;
	}

	//
	// Static factory routines
	//

	/**
	 * @param int $code
	 * @param string $text
	 * @return ErrorType
	 */
	static public function make($code, $text)
	{
		$object = new ErrorType();
		$object->setCode($code);
		$object->setText($text);
		return $object;
	}

	/**
	 * @param int $code
	 * @return ErrorType
	 */
	static public function makeByCode($code)
	{
		$object = new ErrorType();
		$object->setCode($code);
		$object->setText('');
		return $object;
	}

	/**
	 * @param string $text
	 * @return ErrorType
	 */
	static public function makeByText($text)
	{
		$object = new ErrorType();
		$object->setCode(ErrorCode::UNKNOWN);
		$object->setText($text);
		return $object;
	}

	// ...

}
