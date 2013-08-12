<?php

/*
 *  @name    BreedAuthenticationOAuthSignature
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedAuthenticationOAuthSignature extends Konsolidate
{
	public function __construct( $oParent )
	{
		parent::__construct( $oParent );
	}

	public function create( $sSignMethod, $sHTTPMethod, $sURL, $aParam, $sConsumerSecret, $sTokenSecret=null )
	{
		list( $sSignMethod, $sHashMethod ) = explode( "-", $sSignMethod );
		return $this->call(
			"{$sSignMethod}/create",
			$this->_signatureBase(
				strToUpper( $sHTTPMethod ),
				$sURL,
				$aParam
			),
			$this->call( "../Request/urlencode", $sConsumerSecret ),
			$this->call( "../Request/urlencode", !empty( $sTokenSecret ) ? $sTokenSecret : "" ),
			$sHashMethod
		);
	}

	public function identify( $sSignMethod=null )
	{
		list( $sSignMethod, $sHash ) = explode( "-", $sSignMethod );
		return $this->call( "{$sSignMethod}/identify", $sHash );
	}

	protected function _signableParams( $aParam )
	{
		$aParam = array_combine(
			$this->call( "../Request/urlencode", array_keys( $aParam ) ),
			$this->call( "../Request/urlencode", array_values( $aParam ) )
		);

		uksort( $aParam, "strnatcmp" );

		$aReturn = Array();
		foreach( $aParam as $sKey=>$mValue )
			if ( $sKey == "oauth_signature" )
				continue;
			else if ( is_scalar( $mValue ) )
				array_push( $aReturn, "{$sKey}={$mValue}" );
			else if ( is_array( $mValue ) && natsort( $mValue ) )
				foreach( $mValue as $sValue )
					array_push( $aReturn, "{$sKey}={$sValue}" );
		return implode( "&", $aReturn );
	}

	protected function _signatureBase( $sHTTPMethod, $sURL, $aParam )
	{
		return implode(
			"&",
			Array(
				$this->call( "../Request/urlencode", strToUpper( $sHTTPMethod ) ),
				$this->call( "../Request/urlencode", $sURL ),
				$this->call( "../Request/urlencode", $this->_signableParams( $aParam ) )
			)
		);
	}
}