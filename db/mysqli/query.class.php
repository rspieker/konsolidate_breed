<?php

/**
 *  MySQLi result set (this object is instanced and returned for every query)
 *  @name    BreedDBMySQLiQuery
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedDBMySQLiQuery extends Konsolidate
{
	/**
	 *  The connection resource
	 *  @name    _conn
	 *  @type    resource
	 *  @access  protected
	 */
	protected $_conn;

	/**
	 *  The result resource
	 *  @name    _result
	 *  @type    resource
	 *  @access  protected
	 */
	protected $_result;

	/**
	 *  The query
	 *  @name    query
	 *  @type    string
	 *  @access  public
	 */
	public $query;

	/**
	 *  The exception object, used to populate 'error' and 'errno' properties
	 *  @name    exception
	 *  @type    object
	 *  @access  public
	 */
	public $exception;

	/**
	 *  The error message
	 *  @name    error
	 *  @type    string
	 *  @access  public
	 */
	public $error;

	/**
	 *  The error number
	 *  @name    errno
	 *  @type    int
	 *  @access  public
	 */
	public $errno;

	/**
	 *  execute given query on given connection
	 *  @name    execute
	 *  @type    method
	 *  @access  public
	 *  @param   string   query
	 *  @param   resource connection
	 *  @returns void
	 *  @syntax  void BreedDBMySQLiQuery->execute( string query, resource connection )
	 */
	public function execute( $sQuery, &$oConnection )
	{
		$this->query    = $sQuery;
		$this->_conn    = $oConnection;
		$nStart         = microtime( true );
		$this->_result  = $this->_conn->query( $this->query );
		$this->duration = microtime( true ) - $nStart;

		if ( $this->_result instanceof MySQLi_Result )
			$this->rows = $this->_result->num_rows;
		else if ( $this->_result === true )
			$this->rows = $this->_conn->affected_rows;

		//  We want the exception object to tell us everything is going extremely well, don't throw it!
		$this->import( "../exception.class.php" );
		$this->exception = new BreedDBMySQLiException( $this->_conn );
		$this->errno     = &$this->exception->errno;
		$this->error     = &$this->exception->error;
	}

	/**
	 *  rewind the internal resultset
	 *  @name    rewind
	 *  @type    method
	 *  @access  public
	 *  @returns bool success
	 *  @syntax  bool BreedDBMySQLiQuery->rewind()
	 */
	public function rewind()
	{
		if ( $this->_result instanceof MySQLi_Result && $this->_result->num_rows > 0 )
			return $this->_result->data_seek( 0 );
		return false;
	}

	/**
	 *  get the next result from the internal resultset
	 *  @name    next
	 *  @type    method
	 *  @access  public
	 *  @returns object resultrow
	 *  @syntax  object BreedDBMySQLiQuery->next()
	 */
	public function next()
	{
		if ( $this->_result instanceof MySQLi_Result )
			return $this->_result->fetch_object();
		return false;
	}

	/**
	 *  get the ID of the last inserted record
	 *  @name    lastInsertID
	 *  @type    method
	 *  @access  public
	 *  @returns int id
	 *  @syntax  int BreedDBMySQLiQuery->lastInsertID()
	 */
	public function lastInsertID()
	{
		return $this->_conn->insert_id;
	}

	/**
	 *  get the ID of the last inserted record
	 *  @name    lastId
	 *  @type    method
	 *  @access  public
	 *  @returns int id
	 *  @syntax  int BreedDBMySQLiQuery->lastId()
	 *  @note    alias for lastInsertID
	 *  @see     lastInsertID
	 */
	public function lastId()
	{
		return $this->lastInsertID();
	}

	/**
	 *  Retrieve an array containing all resultrows as objects
	 *  @name    fetchAll
	 *  @type    method
	 *  @access  public
	 *  @returns array result
	 *  @syntax  array BreedDBMySQLiQuery->fetchAll()
	 */
	public function fetchAll()
	{
		$aReturn = Array();
		while( $oRecord = $this->next() )
			array_push( $aReturn, $oRecord );
		$this->rewind();
		return $aReturn;
	}

	public function __destruct()
	{
		if ( is_resource( $this->_result ) )
			mysqli_free_result( $this->_result );
	}
}