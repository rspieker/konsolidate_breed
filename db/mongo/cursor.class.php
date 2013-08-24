<?php


class BreedDBMongoCursor extends Konsolidate
{
	protected $_collection;
	protected $_cursor;
	protected $_stack;


	public function __construct(Konsolidate $parent, $collection=null)
	{
		parent::__construct($parent);

		if ($collection)
			$this->_collection = $collection;

		$this->_addToStack('db.' . substr($collection, strrpos((string) $collection, '.') + 1), false);
		$this->_cursor = false;
	}

	public function __call($method, $arg)
	{
		if ($this->_cursor && is_callable(Array($this->_cursor, $method)))
			return $this->_execute($this->_cursor, $method, $arg);
		else if ($this->_collection && is_callable(Array($this->_collection, $method)))
			return $this->_execute($this->_collection, $method, $arg);

		return parent::__call($method, $arg);
	}


	protected function _execute($target, $method, $arg)
	{
		$stack         = $this->_addToStack($method, $arg);
		$this->_cursor = call_user_func_array(Array($target, $method), $arg);
		$stack->end    = microtime(true);

		if (!($this->_cursor instanceof MongoCursor))
			return is_array($this->_cursor) ? (object) $this->_cursor : $this->_cursor;

		return $this;
	}

	protected function _addToStack($method, $arg)
	{
		if (!is_array($this->_stack))
			$this->_stack = Array();

		$stack = (object) Array(
			'start'  => microtime(true)
		);

		if ($arg === false)
		{
			$stack->object = $method;
		}
		else
		{
			$stack->method   = $method;
			$stack->argument = $arg;
			$stack->end      = null;
		}
		$this->_stack[] = $stack;
		return $stack;
	}

	//  Iterator functionality
	public function key()
	{
		if ($this->_cursor instanceof MongoCursor)
			return $this->_cursor->key();
		return false;
	}

	public function current()
	{
		if ($this->_cursor instanceof MongoCursor)
			return (object) $this->_cursor->current();
		return false;
	}

	public function next()
	{
		if ($this->_cursor instanceof MongoCursor)
			return (object) $this->_cursor->next();
		return false;
	}

	public function rewind()
	{
		if ($this->_cursor instanceof MongoCursor)
			return $this->_cursor->rewind();
		return false;
	}
	//  End Iterator functionality
}