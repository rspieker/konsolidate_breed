<?php

/**
 *  Unified access to request variables
 *  @name    BreedRequest
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedRequest extends Konsolidate
{
	public function __construct(Konsolidate $parent)
	{
		parent::__construct($parent);
		$this->_collect();
	}

	/**
	 *  get a property value from a module using a path (including the GET/POST/PUT/etc type modules)
	 *  @name    get
	 *  @type    method
	 *  @access  public
	 *  @param   string   path to the property to get
	 *  @param   mixed    default return value (optional, default null)
	 *  @return  mixed
	 */
	public function get()
	{
		$arg     = func_get_args();
		$key     = array_shift($arg);
		$default = (bool) count($arg) ? array_shift($arg) : null;

		$seperator = strrpos($key, $this->_objectSeparator);
		if ($seperator !== false && ($module = $this->getModule(substr($key, 0, $seperator))) !== false)
		{
			return $module->get(substr($key, $seperator + 1), $default);
		}
		else if ($this->{$_SERVER['REQUEST_METHOD']} && isset($this->{$_SERVER['REQUEST_METHOD']}->{$key}))
		{
			return $this->{$_SERVER['REQUEST_METHOD']}->{$key};
		}
		else if ($this->checkModuleAvailability($key))
		{
			return $this->register($key);
		}
		$return = $this->$key;
		return is_null($return) ? $default : $return; // can (and will be by default!) still be null
	}

	/**
	 *  Create a sub module of the current one, adding the supported request types as transparent instances of type
	 *  @name    instance
	 *  @type    method
	 *  @access  public
	 *  @param   string   modulename
	 *  @param   mixed    param N
	 *  @return  object
	 */
	public function instance($module)
	{
		switch ($module)
		{
			case 'GET':
			case 'POST':
			case 'PUT':
			case 'DELETE':
			case 'PURGE':
				if (!array_key_exists($module, $this->_property))
					$this->_property[$module] = parent::instance('Type', $module);
				return $this->_property[$module];
				break;
		}
		$arg = func_get_args();
		return call_user_func_array(Array('parent', 'instance'), $arg);
	}

	/**
	 *  is the request method PUT
	 *  @name    isPut
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool SiteRequest->isPut()
	 */
	public function isPut()
	{
		return isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'PUT';
	}

	/**
	 *  is the request method DELETE
	 *  @name    isDelete
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool SiteRequest->isDelete()
	 */
	public function isDelete()
	{
		return isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'DELETE';
	}

	/**
	 *  is the request method PURGE
	 *  @name    isPurge
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool SiteRequest->isPurge()
	 */
	public function isPurge()
	{
		return isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'PURGE';
	}


	/**
	 *  Create <Tier>RequestType instances for all appropiate request type and overwrite the superglobal $_REQUEST
	 *  @name    _collect
	 *  @type    method
	 *  @access  protected
	 *  @return  void
	 */
	protected function _collect()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		switch ($method)
		{
			case 'POST':
			case 'PUT':
			case 'DELETE':
				$this->{$method} = parent::instance('Type', $method);
				//  no break, all of these requests may also have GET variables

			case 'GET':
			case 'PURGE':
				$this->GET = parent::instance('Type', 'GET');
				break;

			default:
				$this->call('/Log/message', 'Request-type ' . $method . ' not supported', 3);
				break;
		}

		$GLOBALS['_REQUEST'] = $this;
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
