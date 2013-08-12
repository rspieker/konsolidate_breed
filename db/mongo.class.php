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
	 *  @syntax  bool SiteDBMongo->setConnection( string DSN [, bool newlink ] )
	 */
	public function setConnection( $sURI )
	{
		assert( is_string( $sURI ) );

		$this->_URI  = parse_url( $sURI );
		$this->_conn = false;
		return true;
	}

	/**
	 *  Connect to the database
	 *  @name    connect
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool SiteDBMongo->connect()
	 *  @note    An explicit call to this method is not required, since the query method will create the connection if it isn't connected
	 */
	public function connect()
	{
		if ( !$this->isConnected() )
		{
			if ( !class_exists( "Mongo" ) )
			{
				//  Perhaps fallback onto REST?
				$this->exception( "the PHP extention for Mongo doesn't seem to exist" );
			}

			unset( $this->_URI[ "scheme" ] );
			if ( !isset( $this->_URI[ "host" ] ) || $this->_URI[ "host" ] == "localhost" )
				$this->_mongo = new Mongo();
			else
				$this->_mongo = new Mongo(
					sprintf( "mongodb://%s%s@%s%s",
						isset( $this->_URI[ "user" ] ) ? $this->_URI[ "user" ] : "",
						isset( $this->_URI[ "pass" ] ) ? ":{$this->_URI[ "pass" ]}" : "",
						isset( $this->_URI[ "host" ] ) ? $this->_URI[ "host" ] : "",
						isset( $this->_URI[ "port" ] ) ? ":{$this->_URI[ "port" ]}" : ""
					)
				);

			if ( !is_object( $this->_mongo ) || !( $this->_mongo instanceof Mongo ) )
				$this->exception( "Could not connect to Mongo database" );

			if ( isset( $this->_URI[ "path" ] ) && !empty( $this->_URI[ "path" ] ) )
				$this->_conn = $this->database( trim( $this->_URI[ "path" ], "/" ) );

			if ( $this->_conn === false || !$this->_mongo->connected )
			{
				$this->import( "exception.class.php" );
				$this->error = new BreedMongoException( $this->_conn );
				$this->_conn = null;
				return false;
			}
		}
		return true;
	}

	public function database( $sDatabase )
	{
		if ( !empty( $sDatabase ) )
		{
			$oDatabase = $this->_mongo->{$sDatabase};
			if ( is_object( $oDatabase ) && $oDatabase instanceof MongoDB )
				return $oDatabase;
		}
		return false;
	}

	/**
	 *  Check to see whether a connection is established
	 *  @name    isConnected
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool SiteDBMongo->isConnected()
	 */
	public function isConnected()
	{
		return $this->_conn instanceof MongoDB;
	}

	public function collection( $sCollection )
	{
		if ( $this->connect() )
			return $this->_conn->{$sCollection} instanceof MongoCollection ? $this->_conn->{$sCollection} : false;
		return false;
	}

	public function find( $mCollection, $mQuery, $aField=Array() )
	{
		if ( is_string( $mCollection ) )
			$mCollection = $this->collection( $mCollection );

		if ( !is_array( $mQuery ) )
			$mQuery = Array( "\$id"=>new MongoID( $mQuery ) );

		if ( $mCollection instanceof MongoCollection )
			return $mCollection->find( $mQuery, $aField );

		return false;
	}

	public function findOne( $mCollection, $mQuery, $aField=Array() )
	{
		if ( is_string( $mCollection ) )
			$mCollection = $this->collection( $mCollection );

		if ( $mCollection instanceof MongoCollection )
			return $mCollection->findOne( $mQuery, $aField );

		return false;
	}

	public function insert( $mCollection, $mData )
	{
		if ( is_string( $mCollection ) )
			$mCollection = $this->collection( $mCollection );

		if ( $mCollection instanceof MongoCollection && $mCollection->insert( $mData ) )
			return ( $this->_insertID = ( is_array( $mData ) ? $mData[ "_id" ] : $mData->_id ) );

		return false;
	}

	public function update( $mCollection, $mCondition, $mData )
	{
		if ( is_string( $mCollection ) )
			$mCollection = $this->collection( $mCollection );

		if ( $mCollection instanceof MongoCollection )
			return $mCollection->update( $mCondition, $mData );

		return false;
	}

	public function drop( $mCollection )
	{
		if ( is_string( $mCollection ) )
			$mCollection = $this->collection( $mCollection );

		if ( $mCollection instanceof MongoCollection )
			return $mCollection->drop();

		return false;
	}

	/**
	 *  get the ID of the last inserted record
	 *  @name    lastInsertID
	 *  @type    method
	 *  @access  public
	 *  @returns int id
	 *  @syntax  int BreedDBMongoQuery->lastInsertID()
	 */
	public function lastInsertID()
	{
		return false;
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


	public function represent( $mModule )
	{
		if ( is_string( $mModule ) )
			$mModule = $this->get( $mModule );
//			if ( ( !is_object( $mModule ) || !( $mModule instanceof Iterator ) ) && !is_array( $mModule ) )
//				$this->exception( "Unexpected data type to represent (expecting an Iterator implementation)" );

		$oReturn = new stdClass();
		foreach( $mModule as $sKey=>$mValue )
			if ( preg_match( "/^[a-zA-Z]+/", $sKey ) )
				$oReturn->{$sKey} = $mValue;

		if ( $mModule instanceof Konsolidate )
		{
			$aChild = $mModule->get( "_module" );
			if ( is_array( $aChild ) && (bool) count( $aChild ) )
				foreach( $aChild as $sKey=>$mValue )
					$oReturn->{$mValue->getModulePath( true )} = $this->represent( $mValue );
		}

		return $oReturn;
	}
}