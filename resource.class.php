<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedResource
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: Resource
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 */


/**
 *  Resources
 *  @name    BreedResource
 *  @type    class
 *  @package Kontribute
 *  @author  Rogier Spieker <rogier@konsolidate.net>
 */
class BreedResource extends Konsolidate
{
	/**
	 *  Retrieve the resource id for given path
	 *  @name    getIDByPath
	 *  @type    method
	 *  @access  public
	 *  @param   string path
	 *  @param   bool   create (optional, false)
	 *  @returns int    id
	 *  @syntax  (int)  Object->getIDByPath( string path [, bool create ] )
	 */
	public function getIDByPath( $sPath=null, $bAutoCreate=false )
	{
		if ( empty( $sPath ) )
			$sPath = $this->path;

		$sQuery  = "SELECT rscid,
					       rscenabled
					  FROM resource
					 WHERE rscpath=" . $this->call( "/DB/quote", $sPath );
		$oResult = $this->call( "/DB/query", $sQuery );
		if ( is_object( $oResult ) && $oResult->errno <= 0 && $oResult->rows > 0 )
		{
			//  there can be only one
			$oRecord = $oResult->next();
			$this->id      = (int) $oRecord->rscid;
			$this->path    = $sPath;
			$this->enabled = (bool) $oRecord->rscenabled;
			return $this->id;
		}

		if ( $bAutoCreate )
			return $this->create( $sPath, false );
		return false;
	}

	/**
	 *  Retrieve the path for given resource id
	 *  @name    getPathByID
	 *  @type    method
	 *  @access  public
	 *  @param   int      resource id
	 *  @returns string   path
	 *  @syntax  (string) Object->getPathByID( int resourceid )
	 */
	public function getPathByID( $nID )
	{
		$sQuery  = "SELECT rscpath,
					       rscenabled
					  FROM resource
					 WHERE rscid=" . $this->call( "/DB/quote", $nID );
		$oResult = $this->call( "/DB/query", $sQuery );
		if ( is_object( $oResult ) && $oResult->errno <= 0 && $oResult->rows > 0 )
		{
			//  there can be only one
			$oRecord = $oResult->next();
			$this->id      = (int) $nID;
			$this->path    = (string) $oRecord->rscpath;
			$this->enabled = (bool) $oRecord->rscenabled;
			return $oRecord->rscpath;
		}
		return false;
	}

	/**
	 *  Create a resource
	 *  @name    create
	 *  @type    method
	 *  @access  public
	 *  @param   string  path
	 *  @param   bool    unique (optional, false)
	 *  @returns int     resource id (false on (bool) unique true and already existant
	 *  @syntax  (int)   Object->create( string path [, (bool) unique ] )
	 */
	public function create( $sPath=null, $bUnique=false )
	{
		if ( empty( $sPath ) )
			$sPath = $this->path;

		$sQuery  = "INSERT INTO resource
					       ( rscpath, rsccreatedts )
					VALUES ( " . $this->call( "/DB/quote", $sPath ) . ", NOW() )";
		if ( !$bUnique )
			$sQuery .= " ON DUPLICATE KEY UPDATE rscmodifiedts=NOW()";
		$oResult = $this->call( "/DB/query", $sQuery );
		if ( is_object( $oResult ) && $oResult->errno <= 0 )
			return $oResult->lastInsertID();
		return false;
	}

	/**
	 *  Suggest a sanitized name for use in a path
	 *  @name    suggestSanitizedPath
	 *  @type    method
	 *  @access  public
	 *  @param   string   input
	 *  @param   bool     lowercase
	 *  @returns string   path element suggestion
	 *  @syntax  (string) Object->suggestSanitizedPath( string input [, bool lowercase ] )
	 */
	public function suggestSanitizedPath( $sInput, $bLowerCase=true )
	{
		$aSpecialSuffix = Array( "acute", "grave", "circ", "ring", "tilde", "uml", "lig", "cedil", "slash" );
		for ( $i = 0; $i < count( $aSpecialSuffix ); ++$i )
			$aSpecialSuffix[ $i ] = "/&([a-zA-Z]+){$aSpecialSuffix[ $i ]};/";
		$sInput = htmlentities( utf8_decode( $sInput ) );
		$sInput = preg_replace( $aSpecialSuffix, "\\1", $sInput );
		$sInput = preg_replace( "/[^a-zA-Z0-9_-]+/", "_", html_entity_decode( $sInput ) );
		return $bLowerCase ? strToLower( $sInput ) : $sInput;
	}
}
