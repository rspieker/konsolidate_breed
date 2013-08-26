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
	protected $_verify;
	protected $_type;


	public function __construct($parent, $type=null)
	{
		parent::__construct($parent);
		$this->_type    = strToLower(!empty($type) ? $type : $_SERVER['REQUEST_METHOD']);
		$this->_protect = $this->get('/Config/Request/protect_' . $this->_type, $this->get('/Config/Request/protect', true));
		$this->_verify  = $this->get('/Config/Request/verify_' . $this->_type, $this->get('/Config/Request/verify', true));
		$this->_collect();
	}

	/**
	 *  Enable overwrite protection for all class properties
	 *  @name    __set
	 *  @type    magic method
	 *  @access  public
	 *  @param   string key
	 *  @param   mixed  value
	 *  @return  void
	 */
	public function __set($name, $value)
	{
		if ($this->_protect)
			return $this->call('/Log/message', __METHOD__ . ' not allowed to modify ' . strToUpper($this->_type) . ' request variables', 2);
		return parent::__set($name, $value);
	}

	/**
	 *  Populate the class properties from the collection (and verify the values if _verify is on)
	 *  @name    _populate
	 *  @type    method
	 *  @access  protected
	 *  @param   array   collection
	 *  @param   string  buffer (to verify against)
	 *  @return  void
	 */
	protected function _populate($collection, $buffer=null)
	{
		foreach ($collection as $key=>$value)
			$this->_property[$key] = $this->_verify ? $this->_verifyVariable($buffer, $key, $value) : $value;
	}

	/**
	 *  Obtain the request variables for the desired type
	 *  @name    _collect
	 *  @type    method
	 *  @access  protected
	 *  @return  void
	 */
	protected function _collect()
	{
		//  determine the collection and try to populate it's properties
		switch ($this->_type)
		{
			//  use PHP's built-in _GET and/or _POST superglobals, override after copying
			case 'get':
			case 'post':
				$super = '_' . strToUpper($this->_type);
				if (isset($GLOBALS[$super]) && is_array($GLOBALS[$super]))
				{
					$buffer = $this->_type === 'get' ? $this->call('/Tool/serverVal', 'QUERY_STRING') : trim(file_get_contents('php://input'));
					$this->_populate($GLOBALS[$super], $buffer);
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
					{
						parse_str($raw, $temp);
						$this->_populate($temp, $raw);
					}
				}
				$GLOBALS[$super] = $this;
				break;

			default:
				$this->call('/Log/message', 'Unsupported request type: ' . $this->_type, 1);
				break;
		}
	}

	/**
	 *  Verify given key to exist in the buffer with the same value and strip out NULL bytes from values
	 *  @name    _verifyVariable
	 *  @type    method
	 *  @access  protected
	 *  @param   string buffer
	 *  @param   string key
	 *  @param   mixed  value (one of string or array)
	 *  @return  mixed  value (one of string, array of boolean false if the value is not verified)
 	 *  @note    This method will enhance security but does not guarantee absolute safety, always check user input!
	 */
	protected function _verifyVariable($buffer, $key, $value)
	{
		if (!empty($buffer))
			switch (gettype($value))
			{
				case 'string':
					//  see if the given GET/POST variable can be found in the buffer
					if (preg_match_all('/(' . preg_quote($key) . '=[^&]*)/', $buffer, $match))
					{
						//  prepare the key for easier matching (we need to process the match we put into
						//  parse_str the same way)
						$key = preg_replace('/[\[\]]+/', '_', $key);
						//  traverse last to first, as PHP does in fact overwrite previously set value, it is more
						//  likely to find what we are looking for in the last matches
						for ($i = count($match[0]) - 1; $i >= 0; --$i)
						{
							parse_str(preg_replace('/[\[\]]+/', '_', $match[1][$i]), $test);

							if (isset($test[$key]) && $test[$key] === $value)
							{
								if (strpos($value, "\0"))
									$value = str_replace("\0", '', $value);
								return $value;
							}
						}
					}
					return false;
					break;

				case 'array':
					//  arrays require a little more loving than straight up matching, here we need to match keys and
					//  values, hence an array will stay an array but will get its values set to false if they're
					//  tampered with
					foreach ($value as $k=>$v)
					{
						$test = Array($key . '[' . $k . ']');
						if (is_numeric($k))
							array_unshift($test, $key . '[]');

						foreach ($test as $pattern)
						{
							$value[$k] = $this->_verifyVariable($buffer, $pattern, $v);
							if ($value[$k])
								break;
						}
					}

					return $value;
					break;
			}
		return $value;
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