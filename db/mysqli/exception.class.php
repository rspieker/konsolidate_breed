<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedDBMySQLiException
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: DB/MySQLi/Exception
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 */


/**
 *  MySQLi specific Exception class
 *  @name    BreedDBMySQLiException
 *  @type    class
 *  @package Konsolidate
 *  @author  Rogier Spieker <rogier@konsolidate.net>
 */
class BreedDBMySQLiException extends Exception
{
	/**
	 *  The error message
	 *  @name    error
	 *  @type    string
	 *  @access  public
	 */
	public $error;

	/**
	 *  The error number
	 *  @name    error
	 *  @type    int
	 *  @access  public
	 */
	public $errno;
	
	/**
	 *  constructor
	 *  @name    __construct
	 *  @type    constructor
	 *  @access  public
	 *  @param   resource connection
	 *  @returns object
	 *  @syntax  object = &new BreedDBMySQLiException( resource connection )
	 *  @note    This object is constructed by BreedDBMySQLi as 'status report'
	 */
	public function __construct()
	{
		$aArgs = func_get_args();
		if ( count( $aArgs ) == 2 )
		{
			$this->error = $aArgs[ 0 ];
			$this->errno = $aArgs[ 1 ];
		}
		else
		{
			$oConnection = array_shift( $aArgs );
			$this->error = $oConnection->error;
			$this->errno = $oConnection->errno;
		}
	}
}
