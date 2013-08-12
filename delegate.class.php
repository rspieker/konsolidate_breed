<?php

/*
 *  MySQL table
 *  CREATE TABLE `delegate` (
 *    `dlgid` int(11) unsigned NOT NULL auto_increment,
 *    `dlgcall` varchar(128) default NULL,
 *    `dlgcontent` text,
 *    `dlgmodifiedts` timestamp NOT NULL default CURRENT_TIMESTAMP,
 *    `dlgcreatedts` timestamp NOT NULL default '0000-00-00 00:00:00',
 *    PRIMARY KEY  (`dlgid`),
 *    UNIQUE KEY `dlgcall` (`dlgcall`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8
 */

/**
 *  Delegation class, intended to be used for common actions of which the output remains solid
 *  within a certain timeframe. It caches the serialized result in the database, so it is advised
 *  only to use the Delegate for calls whos duration is significantly longer than the Delegation
 *  needs to retrieve and process it (roughly 10-200 ms depending on your configuration)
 *  @name    BreedDelegate
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedDelegate extends Konsolidate
{
	/**
	 *  The default expiration of cached results in minutes
	 *  @name    DELEGATE_EXPIRATION_MINUTES
	 *  @type    int
	 *  @access  const
	 */
	const DELEGATE_EXPIRATION_MINUTES = 60;

	/**
	 *  The prefered maximum amount of refreshed results per script execution run
	 *  @name    _preferedlimit
	 *  @type    int
	 *  @access  protected
	 *  @note    Can be set through the Config mechanism by setting the "/Config/Delegate/limit"
	 *           to the desired limit, defaults to 2. This is a prefered limit, in case no
	 *           prepared data is available the limit will be exceeded
	 */
	protected $_preferedlimit;

	/**
	 *  The amount of refreshed results this script execution run
	 *  @name    _storecount
	 *  @type    int
	 *  @access  protected
	 */
	protected $_storecount;



	public function __construct( $oParent )
	{
		parent::__construct( $oParent );

		//  get the _preferedlimit from the Config object, defaults to 2
		$this->_preferedlimit = $this->get( "/Config/Delegate/limit", 2 );
		//  set the initial value of _storecount to 0, no store calls done so far
		$this->_storecount    = 0;
	}

	/**
	 *  Start a delegated call
	 *  @name    start
	 *  @type    method
	 *  @access  public
	 *  @param   string   Call to execute/cache
	 *  @param   number   Duration of the cache validity (optional, default null, which falls back to DELEGATE_EXPIRATION_MINUTES)
	 *  @returns mixed
	 *  @syntax  mixed    [Delegate Object]->start( string path [, number validity ] )
	 */
	public function start( $sCall, $nValidity=null )
	{
		//  kick the validity param into the shape we wish to use
		$nValidity = !is_numeric( $nValidity ) ? self::DELEGATE_EXPIRATION_MINUTES : (int) $nValidity;

		//  retrieve the result and return it to
		return $this->_getResult( $sCall, $nValidity );
	}

	/**
	 *  Fetch the cached or refreshed result
	 *  @name    _getResult
	 *  @type    method
	 *  @access  protected
	 *  @param   string   Call to execute/cache
	 *  @param   int      Duration of the cache validity
	 *  @returns mixed
	 *  @syntax  mixed    [Delegate Object]->_getResult( string path, number validity )
	 */
	protected function _getResult( $sCall, $nValidity )
	{
		//  create and execute the database query for retrieving the potential cache
		$sQuery  = "SELECT dlgcontent AS content,
						   IF ( dlgcontent IS NULL OR DATE_ADD( dlgmodifiedts, INTERVAL {$nValidity} MINUTE ) < NOW(), 0, 1 ) AS valid
					  FROM delegate
					 WHERE dlgcall=" . $this->call( "/DB/quote", $sCall );
		$oResult = $this->call( "/DB/query", $sQuery );
		//  we have results
		if ( is_object( $oResult ) && $oResult->errno <= 0 && $oResult->rows > 0 )
		{
			$oRecord = $oResult->next();
			//  if the cached result is still valid, or if there is result AND the amount of fetches this script execution doesn't exceed the limit
			if ( (bool) $oRecord->valid || ( !empty( $oRecord->content ) && ++$this->_storecount > $this->_preferedlimit ) )
				//  throw the result back at the caller
				return unserialize( $oRecord->content );

			//  if there was any result, postpone the next potential fetch
			if ( !empty( $oRecord->content ) )
				$this->_postpone( $sCall );
		}

		//  create and store the call result
		return $this->_storeCallResult( $sCall );
	}

	/**
	 *  Move the modification date to the current time, effectively 'buying time' to refresh the result
	 *  @name    _postpone
	 *  @type    method
	 *  @access  protected
	 *  @param   string Call to execute/cache
	 *  @returns bool
	 *  @syntax  bool   [Delegate Object]->_postpone( string path )
	 */
	protected function _postpone( $sCall )
	{
		//  create and execute the database query for postponing the result so the next visitor doesn't end up doing the same thing
		$sQuery  = "UPDATE delegate
					   SET dlgmodifiedts=NOW()
					 WHERE dlgcall=" . $this->call( "/DB/quote", $sCall );
		$oResult = $this->call( "/DB/query", $sQuery );
		if ( is_object( $oResult ) && $oResult->errno <= 0 )
			return true;
		return false;
	}

	/**
	 *  Fetch the result from the desired call and store it (serialized) in the database
	 *  @name    _storeCallResult
	 *  @type    method
	 *  @access  protected
	 *  @param   string   Call to execute/cache
	 *  @returns mixed
	 *  @syntax  mixed    [Delegate Object]->_storeCallResult( string path )
	 */
	protected function _storeCallResult( $sCall )
	{
		//  store the result in a local variable
		$mResult = $this->call( $sCall );
		//  create and execute the database query for storing the result
		$sQuery  = "INSERT INTO delegate ( dlgcall, dlgcontent, dlgmodifiedts, dlgcreatedts )
					VALUES (
						" . $this->call( "/DB/quote", $sCall ) . ",
						" . $this->call( "/DB/quote", serialize( $mResult ) ) . ",
						NOW(), NOW()
					)
					ON DUPLICATE KEY
					UPDATE dlgcontent=VALUES(dlgcontent),
						   dlgmodifiedts=NOW()";
		$oResult = $this->call( "/DB/query", $sQuery );
		//  something went wrong in a horrible and unforeseen manner, we like to know about it...
		if ( !is_object( $oResult ) || $oResult->errno > 0 )
			$this->call( "/Log/write", "Delegate:could not store result for '{$sCall}', database error ({$oResult->errno}): '{$oResult->error}'" );

		//  return the original result
		return $mResult;
	}
}