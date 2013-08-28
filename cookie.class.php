<?php

/**
 *  Unified access to cookies, validating $_COOKIE variables
 *  @name    BreedCookie
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedCookie extends Konsolidate implements ArrayAccess
{
	protected $_verify;


	public function __construct($parent, $type=null)
	{
		parent::__construct($parent);

		$this->_verify = $this->get('/Config/Cookie/verify', $this->get('/Config/Request/verify', true));
		$this->_collect();
	}

	/**
	 *  set a property in a module using a path
	 *  @name    set
	 *  @type    method
	 *  @access  public
	 *  @param   string   path to the property to set
	 *  @param   mixed    value
	 *  @param   integer  expire   (optional, default end of session)
	 *  @param   string   path     (optional, default current path)
	 *  @param   string   domain   (optional, default current domain)
	 *  @param   bool     secure   (optional, default false)
	 *  @param   bool     httpOnly (options, default false)
	 *  @return  void
	 *  @syntax  BreedCookie->set(string module, mixed value [, integer expire [, string path [, string domain [, bool secure [, bool httpOnly]]]]]);
	 */
	public function set()
	{
		$argument  = func_get_args();
		$property  = array_shift($argument);
		$separator = strrpos($property, $this->_objectSeparator);
		if ($separator !== false && ($instance = $this->getModule(substr($property, 0, $separator))) !== false)
		{
			array_unshift($argument, substr($property, $separator + 1));
			return call_user_func_array(
				Array(
					$instance, // the object
					'set'      // the method
				),
				$argument      // the arguments
			);
		}
		$value    = array_shift($argument);
		$expires  = count($argument) ? array_shift($argument) : null;
		$path     = count($argument) ? array_shift($argument) : null;
		$domain   = count($argument) ? array_shift($argument) : null;
		$secure   = count($argument) ? array_shift($argument) : false;
		$httpOnly = count($argument) ? array_shift($argument) : false;

		$this->_removeCounterfit($property);
		if (setCookie($property, $value, $expires, $path, $domain, $secure, $httpOnly))
			$this->_property[$property] = $value;

		return $this->$property === $value;
	}

	/**
	 *  Enable shorthand cookie setting
	 *  @name    __set
	 *  @type    magic method
	 *  @access  public
	 *  @param   string key
	 *  @param   mixed  value
	 *  @return  void
	 */
	public function __set($name, $value)
	{
		$this->_removeCounterfit($name);
		setCookie($name, $value);
		return parent::__set($name, $value);
	}

	/**
	 *  Enable shorthand cookie removal
	 *  @name    __unset
	 *  @type    magic method
	 *  @access  public
	 *  @param   string key
	 *  @return  void
	 */
	public function __unset($name)
	{
		$this->_removeCounterfit($name);
		if (isset($this->_property[$name]))
		{
			setCookie($name, '');
			unset($this->_property[$name]);
		}
	}

	/**
	 *  Obtain the cookie variables
	 *  @name    _collect
	 *  @type    method
	 *  @access  protected
	 *  @return  void
	 */
	protected function _collect()
	{
		$super = '_COOKIE';
		if (isset($GLOBALS[$super]) && is_array($GLOBALS[$super]))
			$this->_populate($GLOBALS[$super], $this->call('/Tool/serverVal', 'HTTP_COOKIE'));
		$GLOBALS[$super] = $this;
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
			$this->_property[$key] = $this->_verify
				? $this->call('/Input/Verify/bufferValue', $buffer, $key, $value, ';', false)
				: $value;
	}

	/**
	 *  Look for any cookie which would end up internally using the exact same notation but uses a different syntax
	 *  in the cookie header (e.g. 'session_id' and 'session[id' would both end up as 'session_id', hence we remove
	 *  the 'session[id'
	 *  @name    _removeCounterfit
	 *  @type    method
	 *  @access  protected
	 *  @param   array   collection
	 *  @param   string  buffer (to verify against)
	 *  @return  void
	 */
	protected function _removeCounterfit($property)
	{
		if (strpos($property, '_') !== false && preg_match_all('/(' . str_replace('_', '[\[_]', $property) . ')=[^;]*/', $this->call('/Tool/serverVal', 'HTTP_COOKIE'), $match))
			foreach ($match[1] as $pattern)
				if ($pattern !== $property)
					setCookie($pattern, '');
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