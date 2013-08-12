<?php

/**
 *  MySQL Connectivity
 *  @name    BreedDBMySQLi
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedDBMySQLi extends Konsolidate
{
	/**
	 *  The connection URI (parsed url)
	 *  @name    _URI
	 *  @type    array
	 *  @access  protected
	 */
	protected $_URI;

	/**
	 *  The connection resource
	 *  @name    _conn
	 *  @type    resource
	 *  @access  protected
	 */
	protected $_conn;

	/**
	 *  The query cache
	 *  @name    _cache
	 *  @type    array
	 *  @access  protected
	 */
	protected $_cache;

	/**
	 *  Wether or not a transaction is going on
	 *  @name    _transaction
	 *  @type    bool
	 *  @access  protected
	 */
	protected $_transaction;

	/**
	 *  The error object (Exception which isn't thrown)
	 *  @name    error
	 *  @type    object
	 *  @access  public
	 */
	public  $error;

	/**
	 *  Replacements for fingerprinting
	 *  @name    _fingerprintreplacement
	 *  @type    object
	 *  @access  protected
	 */
	protected $_fingerprintreplacement;


	/**
	 *  constructor
	 *  @name    __construct
	 *  @type    constructor
	 *  @access  public
	 *  @param   object parent object
	 *  @returns object
	 *  @syntax  object = &new BreedDBMySQLi( object parent )
	 *  @note    This object is constructed by one of Konsolidates modules
	 */
	public function __construct( $oParent )
	{
		parent::__construct( $oParent );
		$this->_URI             = null;
		$this->_conn            = null;
		$this->_cache           = Array();
		$this->error            = null;
		$this->_transaction     = false;

		$this->_fingerprintreplacement = Array(
			"string"=>$this->get( "/Config/MySQL/fingerprint_string", "'\$'" ),
			"number"=>$this->get( "/Config/MySQL/fingerprint_number", "#" ),
			"NULL"=>$this->get( "/Config/MySQL/fingerprint_null", "NULL" ),
			"names"=>$this->get( "/Config/MySQL/fingerprint_names", "`?`" )
		);
	}

	/**
	 *  Assign the connection DSN
	 *  @name    setConnection
	 *  @type    method
	 *  @access  public
	 *  @param   string DSN URI
	 *  @returns bool
	 *  @syntax  bool BreedDBMySQLi->setConnection( string DSN [, bool newlink ] )
	 */
	public function setConnection( $sURI )
	{
		assert( is_string( $sURI ) );

		$this->_URI = parse_url( $sURI );
		if ( !array_key_exists( "host", $this->_URI ) )
			$this->exception( "Missing required host from the MySQLi DSN '{$sURI}'" );
		else if ( !array_key_exists( "user", $this->_URI ) )
			$this->exception( "Missing required username from the MySQLi DSN '{$sURI}'" );
		return true;
	}

	/**
	 *  Connect to the database
	 *  @name    connect
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool BreedDBMySQLi->connect()
	 *  @note    An explicit call to this method is not required, since the query method will create the connection if it isn't connected
	 */
	public function connect()
	{
		if ( !$this->isConnected() )
		{
			$this->_conn = new MySQLi(
				$this->_URI[ "host" ],
				$this->_URI[ "user" ],
				array_key_exists( "pass", $this->_URI ) ? $this->_URI[ "pass" ] : "",
				trim( $this->_URI[ "path" ], "/" ),
				isset( $this->_URI[ "port" ] ) ? $this->_URI[ "port" ] : 3306
			);

			if ( phpversion() > "5.3.0" ? $this->_conn->connect_error : mysqli_connect_error() )
				$this->exception( $this->_conn->connect_error, $this->_conn->connect_errno );
		}
		return true;
	}

	/**
	 *  Disconnect from the database
	 *  @name    disconnect
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool BreedDBMySQLi->disconnect()
	 */
	public function disconnect()
	{
		if ( $this->isConnected() )
			return $this->_conn->close();
		return true;
	}

	/**
	 *  Check to see whether a connection is established
	 *  @name    isConnected
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool BreedDBMySQLi->isConnected()
	 */
	public function isConnected()
	{
		return is_object( $this->_conn );
	}

	/**
	 *  Query the database
	 *  @name    query
	 *  @type    method
	 *  @access  public
	 *  @param   string query
	 *  @paran   bool   usecache (default true)
	 *  @paran   bool   add info (default false)
	 *  @paran   bool   extended info (default true)
	 *  @returns object result
	 *  @syntax  object BreedDBMySQLi->query( string query [, bool usecache [, bool addinfo [, bool extendedinfo ] ] ] )
	 *  @note    Requesting extended information will automatically enable normal info aswel
	 */
	public function query( $sQuery, $bUseCache=true, $bAddInfo=false, $bExtendedInfo=false )
	{
		$sCacheKey = md5( $sQuery );
		if ( $bUseCache && array_key_exists( $sCacheKey, $this->_cache ) )
		{
			$this->_cache[ $sCacheKey ]->rewind();
			$this->_cache[ $sCacheKey ]->cached = true;
			return $this->_cache[ $sCacheKey ];
		}

		if ( $this->connect() )
		{
			$oQuery = $this->instance( "Query" );
			$oQuery->execute( $sQuery, $this->_conn );
			$oQuery->info   = $bAddInfo || $bExtendedInfo ? $this->info( $bExtendedInfo, Array( "duration"=>$oQuery->duration ) ) : "additional query info not processed";
			$oQuery->cached = false;

			if ( $bUseCache && $this->_isCachableQuery( $sQuery ) )
				$this->_cache[ $sCacheKey ] = $oQuery;
			return $oQuery;
		}
		return false;
	}

	/**
	 *  create a fingerprint for given query, attempting to remove all variable components
	 *  @name    fingerprint
	 *  @type    method
	 *  @access  public
	 *  @param   string   query
	 *  @param   bool     hash output (default true)
	 *  @param   bool     strip escaped names (default false)
	 *  @returns string   fingerprint
	 *  @syntax  string BreedDBMySQLiQuery->fingerprint( string query [, bool hash [, bool stripnames ] ] )
	 */
	public function fingerprint( $sQuery, $bHash=true, $bStripNames=false, $aReplace=Array() )
	{
		$aReplace = array_merge( $this->_fingerprintreplacement, $aReplace );
		$sString  = $aReplace[ "string" ];
		$sNumber  = $aReplace[ "number" ];
		$sNULL    = $aReplace[ "NULL" ];
		$sNames   = $aReplace[ "names" ];
		$aReplace = Array(
			'/([\"\'])(?:.*[^\\\\]+)*(?:(?:\\\\{2})*)+\1/xU'=>$sString, //  replace quoted variables
			"/--.*?[\r\n]+/"=>"",                                       //  strip '--' comments
			"/#.*?[\r\n]+/"=>"",                                        //  strip '#' comments
			"/\/\*.*?\*\//"=>"",                                        //  strip /* */ comments
			"/\s*([\(\)=\+\/,-]+)/"=>"\\1",                             //  strip whitespace left of specific chars
			"/([\(=\+\/,-]+)\s*/"=>"\\1",                               //  strip whitespace right of specific chars
			"/\b[0-9]*[\.]*[0-9]+\b/"=>$sNumber,                        //  replace numbers which appear to be values
			"/\bNULL\b/i"=>$sNULL,                                      //  replace NULL values
			"/\s+/"=>" "                                                //  replace (multiple) whitespace chars by space
		);
		if ( $bStripNames )
			$aReplace[ "/`.*?`/" ] = $sNames;
		$sReturn = trim( preg_replace( array_keys( $aReplace ), array_values( $aReplace ), $sQuery ) );
		return $bHash ? md5( $sReturn ) : $sReturn;
	}

	/**
	 *  get the ID of the last inserted record
	 *  @name    lastInsertID
	 *  @type    method
	 *  @access  public
	 *  @returns int id
	 *  @syntax  int BreedDBMySQLi->lastInsertID()
	 */
	public function lastInsertID()
	{
		if ( $this->isConnected() )
			return mysqli_insert_id( $this->_conn );
		return false;
	}

	/**
	 *  get the ID of the last inserted record
	 *  @name    lastId
	 *  @type    method
	 *  @access  public
	 *  @returns int id
	 *  @syntax  int BreedDBMySQLi->lastId()
	 *  @note    alias for lastInsertID
	 *  @see     lastInsertID
	 */
	public function lastId()
	{
		return $this->lastInsertID();
	}

	/**
	 *  Properly escape a string
	 *  @name    escape
	 *  @type    method
	 *  @access  public
	 *  @param   string input
	 *  @returns string escaped input
	 *  @syntax  string BreedDBMySQLi->escape( string input )
	 */
	public function escape( $sString )
	{
		if ( $this->connect() )
			return mysqli_real_escape_string( $this->_conn, $sString );

		$this->call( "/Log/write", get_class( $this ) . "::escape, could not escape string" );
		return false;
	}

	/**
	 *  Quote and escape a string
	 *  @name    quote
	 *  @type    method
	 *  @access  public
	 *  @param   string input
	 *  @returns string quoted escaped input
	 *  @syntax  string BreedDBMySQLi->quote( string input )
	 */
	public function quote( $sString )
	{
		return "'" . $this->escape( $sString ) . "'";
	}

	/**
	 *  Start transaction
	 *  @name    startTransaction
	 *  @type    method
	 *  @access  public
	 *  @returns bool success
	 *  @syntax  bool BreedDBMySQLi->startTransaction()
	 */
	public function startTransaction()
	{
		if ( $this->connect() && !$this->_transaction )
			$this->_transaction = $this->_conn->autocommit( false );
		return $this->_transaction;
	}

	/**
	 *  End transaction by sending 'COMMIT' or 'ROLLBACK'
	 *  @name    startTransaction
	 *  @type    method
	 *  @access  public
	 *  @param   bool commit [optional, default true]
	 *  @returns bool success
	 *  @syntax  bool BreedDBMySQLi->endTransaction( bool commit )
	 *  @note    if argument 'commit' is true, 'COMMIT' is sent, 'ROLLBACK' otherwise
	 */
	public function endTransaction( $bSuccess=true )
	{
		if ( $this->_transaction )
			$this->_transaction = !( $bSuccess ? $this->_conn->commit() : $this->_conn->rollback() );
		return !$this->_transaction;
	}

	/**
	 *  Commit a transaction
	 *  @name    commitTransaction
	 *  @type    method
	 *  @access  public
	 *  @returns bool success
	 *  @syntax  bool BreedDBMySQLi->commitTransaction()
	 *  @note    same as endTransaction( true );
	 */
	public function commitTransaction()
	{
		return $this->endTransaction( true );
	}

	/**
	 *  Rollback a transaction
	 *  @name    rollbackTransaction
	 *  @type    method
	 *  @access  public
	 *  @returns bool success
	 *  @syntax  bool BreedDBMySQLi->rollbackTransaction()
	 *  @note    same as endTransaction( false );
	 */
	public function rollbackTransaction()
	{
		return $this->endTransaction( false );
	}



	//  As MySQLi has a lot more to offer than MySQL, we provide extra methods
	//  NOTE: be aware that by using these methods you will loose some flawless compatibility
	//        with the normal MySQL engine.


	/**
	 *  Returns the default character set for the database connection
	 *  @name    characterSetName
	 *  @type    method
	 *  @access  public
	 *  @returns string characterset
	 *  @syntax  string BreedDBMySQLi->characterSetName()
	 */
	public function characterSetName()
	{
		return $this->_conn->character_set_name();
	}

	/**
	 *  Returns the MySQLi client version
	 *  @name    clientVersion
	 *  @type    method
	 *  @access  public
	 *  @param   bool versionstring [optional, default false]
	 *  @returns int  version
	 *  @syntax  int  BreedDBMySQLi->clientVersion( [bool versionstring] )
	 */
	public function clientVersion( $bVersionString=false )
	{
		if ( $bVersionString )
			return $this->_versionToString( $this->_conn->client_version );
		return $this->_conn->client_version;
	}

	/**
	 *  Returns the MySQLi client info
	 *  @name    clientInfo
	 *  @type    method
	 *  @access  public
	 *  @returns string info
	 *  @syntax  string BreedDBMySQLi->clientInfo()
	 *  @note    the client info may appear to be the version as string, but can contain
	 *           additional build information, use clientVersion( true ) for fool-proof
	 *           string version comparing
	 */
	public function clientInfo()
	{
		return $this->_conn->client_info;
	}

	/**
	 *  Returns the MySQLi server version
	 *  @name    serverVersion
	 *  @type    method
	 *  @access  public
	 *  @param   bool versionstring [optional, default false]
	 *  @returns int  version (false if a connection could not be established)
	 *  @syntax  int  BreedDBMySQLi->serverVersion( [bool versionstring] )
	 */
	public function serverVersion( $bVersionString=false )
	{
		if ( !$this->connect() )
			return false;

		if ( $bVersionString )
			return $this->_versionToString( $this->_conn->server_version );
		return $this->_conn->server_version;
	}

	/**
	 *  Returns the MySQLi server info
	 *  @name    serverInfo
	 *  @type    method
	 *  @access  public
	 *  @returns string info
	 *  @syntax  string BreedDBMySQLi->serverInfo()
	 *  @note    the server info may appear to be the version as string, but can contain
	 *           additional build information, use serverVersion( true ) for fool-proof
	 *           string version comparing
	 */
	public function serverInfo()
	{
		return $this->_conn->server_info;
	}

	/**
	 *  Retrieves information about the most recently executed query
	 *  @name    info
	 *  @type    method
	 *  @access  public
	 *  @param   bool extendedinfo [optional, default false]
	 *  @returns object info
	 *  @syntax  object BreedDBMySQLi->info( [bool extendedinfo] )
	 *  @note    by requesting extended info, the connection stats are added to the info object
	 */
	public function info( $bExtendInfo=false, $aAppendInfo=null )
	{
		$oReturn = $this->instance( "Info" );
		$oReturn->process( $this->_conn, $bExtendInfo, $aAppendInfo );
		return $oReturn;
	}



	/**
	 *  Convert the given version integer back to its string representation
	 *  @name    _versionToString
	 *  @type    method
	 *  @access  protected
	 *  @param   int    version
	 *  @returns string version
	 *  @syntax  string BreedDBMySQLi->clientVersion( [bool versionstring] )
	 */
	protected function _versionToString( $nVersion )
	{
		$nMain  = round( $nVersion / 10000 );
		$nMinor = round( ( $nVersion - ( $nMain * 10000 ) ) / 100 );
		return "{$nMain}.{$nMinor}." . ( $nVersion - ( ( $nMain * 10000 ) + ( $nMinor * 100 ) ) );
	}

	/**
	 *  Determine whether a query should be cached (this applies only to 'SELECT' queries)
	 *  @name    _isCachableQuery
	 *  @type    method
	 *  @access  protected
	 *  @param   string query
	 *  @returns bool   success
	 *  @syntax  bool BreedDBMySQLi->_isCachableQuery( string query )
	 */
	protected function _isCachableQuery( $sQuery )
	{
		return (bool) preg_match( "/^\s*SELECT /i", $sQuery );
	}
}