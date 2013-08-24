<?php

/**
 *  Mongo Connectivity
 *  @name    BreedDBMongo
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedDBMongo extends Konsolidate
{
	protected $_conn;     // Mongo
	protected $_database; // MongoDB
	protected $_URI;
	protected $_insertID;

	/**
	 *  Assign the connection DSN
	 *  @name    setConnection
	 *  @type    method
	 *  @access  public
	 *  @param   string DSN URI
	 *  @param   bool   force new link [optional, default false]
	 *  @returns bool
	 *  @syntax  bool BreedDBMongo->setConnection(string DSN [, bool newlink])
	 */
	public function setConnection($uri)
	{
		assert(is_string($uri));

		$this->_URI  = parse_url($uri);
		$this->_conn = false;
		return true;
	}

	/**
	 *  Connect to the database
	 *  @name    connect
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool BreedDBMongo->connect()
	 *  @note    An explicit call to this method is not required, since the query methods will create the connection if it isn't connected
	 */
	public function connect()
	{
		if (!$this->isConnected())
		{
			if (!class_exists('Mongo'))
			{
				//  Perhaps fallback onto REST?
				$this->exception('the PHP extention for Mongo doesn\'t seem to exist');
			}

			unset($this->_URI['scheme']);
			if (!isset($this->_URI['host']) || $this->_URI['host'] == 'localhost')
				$this->_mongo = new Mongo();
			else
				$this->_mongo = new Mongo(
					sprintf('mongodb://%s%s%s%s%s',
						isset($this->_URI['user']) ? $this->_URI['user'] : '',
						isset($this->_URI['pass']) ? ':' . $this->_URI['pass'] : '',
						isset($this->_URI['user']) || isset($this->_URI["pass"]) ? '@' : '',
						isset($this->_URI['host']) ? $this->_URI['host'] : '',
						isset($this->_URI['port']) ? ':' . $this->_URI['port'] : ''
					)
				);

			if (!is_object($this->_mongo) || !($this->_mongo instanceof Mongo))
				$this->exception('Could not connect to Mongo database');

			if (isset($this->_URI['path']) && !empty($this->_URI['path']))
				$this->_conn = $this->database(trim($this->_URI['path'], '/'));

			if ($this->_conn === false || !$this->_mongo->connected)
			{
				$this->import('exception.class.php');
				$this->error = new BreedMongoException($this->_conn);
				$this->_conn = null;
				return false;
			}
		}
		return true;
	}

	public function database($name)
	{
		if (!empty($name))
		{
			$db = $this->_mongo->{$name};
			if ($db instanceof MongoDB)
				return $db;
		}
		return false;
	}

	/**
	 *  Check to see whether a connection is established
	 *  @name    isConnected
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool BreedDBMongo->isConnected()
	 */
	public function isConnected()
	{
		return $this->_conn instanceof MongoDB;
	}

	public function collection($name)
	{
		if ($this->connect())
			return $this->_conn->{$name} instanceof MongoCollection ? $this->_conn->{$name} : false;
		return false;
	}

	public function find($collection, $search=Array(), $field=Array())
	{
		if (is_string($collection))
			$collection = $this->collection($collection);

		if ($collection instanceof MongoCollection)
		{
			$cursor = $this->instance('Cursor', $collection);
			return $cursor->find($search, $field);
		}

		return false;
	}

	public function findOne($collection, $search=Array(), $field=Array())
	{
		if (is_string($collection))
			$collection = $this->collection($collection);

		if ($collection instanceof MongoCollection)
		{
			$cursor = $this->instance('Cursor', $collection);
			return $cursor->findOne($search, $field);
		}

		return false;
	}

	public function insert($collection, $record)
	{
		if (is_string($collection))
			$collection = $this->collection($collection);

		if ($collection instanceof MongoCollection && $collection->insert($record))
		{
			$this->_insertID = is_array($record) ? $record['_id'] : $record->_id;
			return $record;
		}

		return false;
	}

	public function update($collection, $condition, $data, $option=null)
	{
		if (is_string($collection))
			$collection = $this->collection($collection);

		if ($collection instanceof MongoCollection)
			return $collection->update($condition, $data, $option);

		return false;
	}

	public function drop($collection)
	{
		if (is_string($collection))
			$collection = $this->collection($collection);

		if ($collection instanceof MongoCollection)
			return $collection->drop();

		return false;
	}

	/**
	 *  get the ID of the last inserted record
	 *  @name    lastInsertID
	 *  @type    method
	 *  @access  public
	 *  @returns int id
	 *  @syntax  int BreedDBMongo->lastInsertID()
	 */
	public function lastInsertID()
	{
		return $this->_insertID;
	}

	/**
	 *  get the ID of the last inserted record
	 *  @name    lastId
	 *  @type    method
	 *  @access  public
	 *  @returns int id
	 *  @syntax  int CoreDBMySQLQuery->lastId()
	 *  @note    alias for lastInsertID
	 *  @see     lastInsertID
	 */
	public function lastId()
	{
		return $this->lastInsertID();
	}


	public function represent($module)
	{
		if (is_string($module))
			$module = $this->get($module);
//			if ((!is_object($module) || !($module instanceof Iterator)) && !is_array($module))
//				$this->exception("Unexpected data type to represent (expecting an Iterator implementation)");

		$return = new stdClass();
		foreach($module as $key=>$value)
			if (preg_match("/^[a-zA-Z]+/", $key))
				$return->{$key} = $value;

		if ($module instanceof Konsolidate)
		{
			$child = $module->get('_module');
			if (is_array($child) && (bool) count($child))
				foreach($child as $key=>$value)
					$return->{$value->getModulePath(true)} = $this->represent($value);
		}

		return $return;
	}
}