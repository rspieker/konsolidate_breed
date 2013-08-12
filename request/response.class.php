<?php

/**
 *  @name     BreedRequestResponse
 *  @type     class
 *  @package  Breed
 *  @author   Rogier Spieker <rogier@konfirm.net>
 */
class BreedRequestResponse extends Konsolidate
{
	const DEFAULT_HTTP_METHOD="GET";

	protected $_timeout;


	public function __construct( $oParent )
	{
		parent::__construct( $oParent );

		$this->_timeout = 10;
	}

	public function execute( $sURL, $aParam, $aHeader, $sHTTPMethod=null )
	{
		if ( empty( $sHTTPMethod ) )
			$sHTTPMethod = self::DEFAULT_HTTP_METHOD;

		$aOption = Array(
			CURLOPT_HTTPHEADER=>$this->_createHeaderList( $aHeader ),
			CURLOPT_HEADER=>true,
			CURLOPT_FOLLOWLOCATION=>true,
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_SSL_VERIFYPEER=>false,
			CURLOPT_TIMEOUT=>$this->_timeout
		);

		switch( strToUpper( $sHTTPMethod ) )
		{
			case "GET":
				if ( is_array( $aParam ) && (bool) count( $aParam ) )
					$sURL .= "?" . str_replace( "&amp;", "&", http_build_query( $aParam ) );
				break;
			case "POST":
				$aOption[ CURLOPT_POST ] = true;
				if ( is_array( $aParam ) && count( $aParam ) )
					$aOption[ CURLOPT_POSTFIELDS ] = $aParam;
				break;
		}

		$oCURL = curl_init( $sURL );
		curl_setopt_array( $oCURL, $aOption );

		$sResponse = curl_exec( $oCURL );
		if ( $sResponse )
		{
			if ( substr( $sResponse, 0, 21 ) == "HTTP/1.1 100 Continue" )
				list( $sDump, $sResponse ) = explode( "\r\n\r\n", $sResponse, 2 );

			list( $aHeader, $this->data ) = explode( "\r\n\r\n", $sResponse, 2 );
			$this->status = curl_getinfo( $oCURL, CURLINFO_HTTP_CODE );
			$this->header = $this->_processResponseHeaders( explode( "\n", $aHeader ) );
			curl_close( $oCURL );
			return true;
		}

		return false;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getHeader( $sName=null )
	{
		if ( empty( $sName ) )
			return $this->header;
		else if ( array_key_exists( "header", $this->_property ) && array_key_exists( strToUpper( $sName ), $this->_property[ "header" ] ) )
			return $this->_property[ "header" ][ strToUpper( $sName ) ];
		return false;
	}

	protected function _createHeaderList( $aHeader=null )
	{
		$aReturn = Array();
		if ( is_array( $aHeader ) && (bool) count( $aHeader ) )
			foreach( $aHeader as $sKey=>$sValue )
				if ( is_numeric( $sKey ) )
					array_push( $aReturn, $sValue );
				else if ( is_scalar( $sValue ) && !empty( $sValue ) )
					array_push( $aReturn, "{$sKey}: {$sValue}" );
		return $aReturn;
	}

	protected function _processResponseHeaders( $aHeader )
	{
		$aReturn = Array();
		for ( $i = 1; $i < count( $aHeader ); ++$i )
		{
			list( $sKey, $sValue ) = explode( ":", $aHeader[ $i ], 2 );
			$aReturn[ strToUpper( $sKey ) ] = trim( $sValue );
		}
		return $aReturn;
	}
}
