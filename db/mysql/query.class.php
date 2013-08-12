<?php

/**
 *  MySQL result set (this object is instanced and returned for every query)
 *  @name    BreedDBMySQLQuery
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedDBMySQLQuery extends CoreDBMySQLQuery
{
	/**
	 *  execute given query on given connection
	 *  @name    execute
	 *  @type    method
	 *  @access  public
	 *  @param   string   query
	 *  @param   resource connection
	 *  @returns void
	 *  @syntax  void BreedDBMySQLQuery->execute( string query, resource connection )
	 */
	public function execute( $sQuery, &$rConnection )
	{
		$this->query       = $sQuery;
		$this->_conn       = $rConnection;
		$nStart            = microtime( true );
		$this->_result     = @mysql_query( $this->query, $this->_conn );
		$this->duration    = microtime( true ) - $nStart;

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

	public function __destruct()
	{
		if ( is_resource( $this->_result ) )
			mysql_free_result( $this->_result );
	}
}