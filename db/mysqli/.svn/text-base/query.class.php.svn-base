<?php

	/*
	 *            ________ ___        
	 *           /   /   /\  /\       Konsolidate
	 *      ____/   /___/  \/  \      
	 *     /           /\      /      http://www.konsolidate.net
	 *    /___     ___/  \    /       
	 *    \  /   /\   \  /    \       Class:  CoreDBMySQLQuery
	 *     \/___/  \___\/      \      Tier:   Core
	 *      \   \  /\   \  /\  /      Module: DB/MySQL/Query
	 *       \___\/  \___\/  \/       
	 *         \          \  /        $Rev$
	 *          \___    ___\/         $Author$
	 *              \   \  /          $Date$
	 *               \___\/           
	 */


	/**
	 *  MySQL result set (this object is instanced and returned for every query)
	 *  @name    CoreDBMySQLQuery
	 *  @type    class
	 *  @package Konsolidate
	 *  @author  Rogier Spieker <rogier@konsolidate.net>
	 */
	class CoreDBMySQLQuery extends Konsolidate
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
		 *  @syntax  void CoreDBMySQLQuery->execute( string query, resource connection )
		 */
		public function execute( $sQuery, &$rConnection )
		{
			$this->query   = $sQuery;
			$this->_conn   = $rConnection;
			$this->_result = @mysql_query( $this->query, $this->_conn );

			if ( is_resource( $this->_result ) )
				$this->rows = mysql_num_rows( $this->_result );
			else if ( $this->_result === true )
				$this->rows = mysql_affected_rows( $this->_conn );

			//  We want the exception object to tell us everything is going extremely well, don't throw it!
			$this->import( "../exception.class.php" );
			$this->exception = new CoreDBMySQLException( $this->_conn );
			$this->errno     = &$this->exception->errno;
			$this->error     = &$this->exception->error;
		}

		/**
		 *  rewind the internal resultset
		 *  @name    rewind
		 *  @type    method
		 *  @access  public
		 *  @returns bool success
		 *  @syntax  bool CoreDBMySQLQuery->rewind()
		 */
		public function rewind()
		{
			if ( is_resource( $this->_result ) && mysql_num_rows( $this->_result ) > 0 )
				return mysql_data_seek( $this->_result, 0 );
			return false;
		}

		/**
		 *  get the next result from the internal resultset
		 *  @name    next
		 *  @type    method
		 *  @access  public
		 *  @returns object resultrow
		 *  @syntax  object CoreDBMySQLQuery->next()
		 */
		public function next()
		{
			if ( is_resource( $this->_result ) )
				return mysql_fetch_object( $this->_result );
			return false;
		}

		/**
		 *  get the ID of the last inserted record
		 *  @name    lastInsertID
		 *  @type    method
		 *  @access  public
		 *  @returns int id
		 *  @syntax  int CoreDBMySQLQuery->lastInsertID()
		 */
		public function lastInsertID()
		{
			return mysql_insert_id( $this->_conn );
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

		/**
		 *  Retrieve an array containing all resultrows as objects
		 *  @name    fetchAll
		 *  @type    method
		 *  @access  public
		 *  @returns array result
		 *  @syntax  array CoreDBMySQLQuery->fetchAll()
		 */
		public function fetchAll()
		{
			$aReturn = Array();
			while( $oRecord = $this->next() )
				array_push( $aReturn, $oRecord );
			$this->rewind();
			return $aReturn;
		}
	}

?>