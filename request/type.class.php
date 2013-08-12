<?php

/**
 *  Unified access to request type buffers such as $_GET and $_POST
 *  @name    BreedRequestType
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedRequestType extends Konsolidate implements ArrayAccess
{
	protected $_protect;
	protected $_type;

	public function __construct($parent, $type=null)
	{
		parent::__construct($parent);
		$this->_type    = strToLower(!empty($type) ? $type : $_SERVER['REQUEST_METHOD']);
		$this->_protect = $this->get('/Config/Request/protect_' . $this->_type, $this->get('/Config/Request/protect', true));
		$this->_collect();
	}

	protected function _collect()
	{
		//  determine the collection and try to populate it's properties
		switch (strToLower($this->_type))
		{
			//  use PHP's built-in _GET and/or _POST superglobals, override after copying
			case 'get':
			case 'post':
				$super = '_' . strToUpper($this->_type);
				if (isset($GLOBALS[$super]) && is_array($GLOBALS[$super]))
				{
					$this->_property = array_merge($this->_property, $GLOBALS[$super]);
				}
				$GLOBALS[$super] = $this;
				break;

			//  provide PUT and DELETE support
			case 'put':
			case 'delete':
				$super = '_' . strToUpper($this->_type);
				if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == strToUpper($this->_type))
				{
					$raw = trim(file_get_contents("php://input"));
					if (!empty($raw))
						parse_str($raw, $this->_property);
				}
				$GLOBALS[$super] = $this;
				break;

			default:
				$this->call('/Log/message', 'Unsupported request type: ' . $this->_type, 1);
				break;
		}
	}

	public function __set($name, $value)
	{
		if ($this->_protect)
		{
			return $this->call('/Log/message', __METHOD__ . ' not allowed to modify ' . strToUpper($this->_type) . ' request variables', 2);
		}
		return parent::__set($name, $value);
	}


	/*  ArrayAccess implementation */
	public function offsetGet($offset)
	{
		return $this->{$offset};
	}

	public function offsetSet($offset, $value)
	{
		return $this->{$offset} = $value;
	}

	public function offsetExists($offset)
	{
		return isset($this->{$offset});
	}

	public function offsetUnset($offset)
	{
		unset($this->{$offset});
	}
}