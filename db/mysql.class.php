<?php

/**
 *  Resources
 *  @name    BreedDBMySQL
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedDBMySQL extends CoreDBMySQL
{
	protected $_fingerprintreplacement;

	public function __construct( $oParent )
	{
		parent::__construct( $oParent );

		$this->_fingerprintreplacement = Array(
			"string"=>$this->get( "/Config/MySQL/fingerprint_string", "'\$'" ),
			"number"=>$this->get( "/Config/MySQL/fingerprint_number", "#" ),
			"NULL"=>$this->get( "/Config/MySQL/fingerprint_null", "NULL" ),
			"names"=>$this->get( "/Config/MySQL/fingerprint_names", "`?`" )
		);
	}

	/**
	 *  Query the database
	 *  @name    query
	 *  @type    method
	 *  @access  public
	 *  @param   string query
	 *  @paran   bool   usecache [optional, default true]
	 *  @returns object result
	 *  @syntax  object BreedDBMySQL->query( string query [, bool usecache ] )
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

			$oQuery->cached = false;

			if ( $bUseCache && $this->_isCachableQuery( $sQuery ) )
			{
				if (count($this->_cache) > 100)
					$this->_cache = array_slice($this->_cache, -100, 100, true);
				$this->_cache[ $sCacheKey ] = $oQuery;
			}

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
	 *  @syntax  string BreedDBMySQLQuery->fingerprint( string query [, bool hash [, bool stripnames ] ] )
	 */
	public function fingerprint( $sQuery, $bHash=true, $bStripNames=false )
	{
		$sString  = $this->_fingerprintreplacement[ "string" ];
		$sNumber  = $this->_fingerprintreplacement[ "number" ];
		$sNULL    = $this->_fingerprintreplacement[ "NULL" ];
		$sNames   = $this->_fingerprintreplacement[ "names" ];
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
}