<?php

/*
 *  @name    BreedAuthenticationOAuthRequest
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedAuthenticationOAuthRequest extends Konsolidate
{
	const DEFAULT_HTTP_METHOD = "POST";
	const DEFAULT_SIGN_METHOD = "HMAC-SHA1";

	public $url;
	public $method;


	public function getResponseData( $sURL, $aParam, $oConsumer, $oToken=null, $sHTTPMethod=null, $sSignatureMethod="HMAC-SHA1" )
	{
		$oReturn = $this->getResponse( $sURL, $aParam, $oConsumer, $oToken, $sHTTPMethod, $sSignatureMethod );
		if ( $oReturn && $oReturn->getStatus() == "200" )
			return $oReturn->getData();
		return false;
	}

	public function getResponseHeaders( $sURL, $aParam, $oConsumer, $oToken=null, $sHTTPMethod=null, $sSignatureMethod="HMAC-SHA1" )
	{
		$oReturn = $this->getResponse( $sURL, $aParam, $oConsumer, $oToken, $sHTTPMethod, $sSignatureMethod );
		if ( $oReturn && $oReturn->getStatus() == "200" )
			return $oReturn->getHeader();
		return false;
	}

	public function createParameters( $sURL, $aParam, $oConsumer, $oToken=null, $sHTTPMethod=null, $sSignatureMethod="HMAC-SHA1" )
	{
		//  Set default HTTP method in case none is provided
		if ( empty( $sHTTPMethod ) )
			$sHTTPMethod = self::DEFAULT_HTTP_METHOD;

		//  Set all 'variable' OAuth variables
		$aOAuth = Array(
			"oauth_signature_method"=>$this->call( "../Signature/identify", $sSignatureMethod ),
			"oauth_consumer_key"=>$oConsumer->key
		);
		if ( is_object( $oToken ) )
			$aOAuth[ "oauth_token" ] = $oToken->key;

		//  Merge all request variables
		$aParam = array_merge(
			is_array( $aParam ) ? $aParam : Array(),
			$this->_getDefaultParams(),
			$aOAuth
		);

		//  Create the OAuth signature and add it to the request variables and return the full URL
		$aParam[ "oauth_signature" ] = $this->call(
			"../Signature/create",
			$sSignatureMethod,
			$sHTTPMethod,
			$sURL,
			$aParam,
			$oConsumer->secret,
			is_object( $oToken ) ? $oToken->secret : null
		);

		return $aParam;
	}

	public function getResponse( $sURL, $aParam, $oConsumer, $oToken=null, $sHTTPMethod=null, $sSignatureMethod="HMAC-SHA1" )
	{
		$aParam = $this->createParameters(
			$sURL,
			$aParam,
			$oConsumer,
			$oToken,
			$sHTTPMethod,
			$sSignatureMethod
		);
		$oReturn = $this->instance( "/Request/Response" );
		if ( $oReturn->execute( $this->_sanitizeURL( $sURL ), $aParam, null, $sHTTPMethod ) )
			return $oReturn;
		return false;
	}

	protected function _getDefaultParams()
	{
		return Array(
			"oauth_nonce"=>$this->_getNonce(),
			"oauth_timestamp"=>time(),
			"oauth_version"=>$this->get( "../version" )
		);
	}

	protected function _getNonce()
	{
		return md5( mt_rand() . microtime() );
	}

	protected function _sanitizeMethod( $sMethod )
	{
		return strToUpper( $sMethod );
	}

	protected function _sanitizeURL( $sURL )
	{
		$aPart   = parse_url( $sURL );
		$sScheme = CoreTool::arrayVal( "scheme", $aPart, "http" );
		$nPort   = (int) CoreTool::arrayVal( "port", $aPart, $sScheme == "https" ? 443 : 80 );
		$sHost   = CoreTool::arrayVal( "host", $aPart ) . ( ( $sScheme == "https" && $nPort == 443 ) || ( $sScheme == "http" && $nPort == 80 ) ? "" : ":{$nPort}" );
		$sPath   = CoreTool::arrayVal( "path", $aPart );

		return "{$sScheme}://{$sHost}{$sPath}";
	}

	protected function _createQueryString( $aParam, $sAppendTo=null )
	{
		if ( is_array( $aParam ) && (bool) count( $aParam ) )
		{
			$aParam = array_combine(
				$this->urlencode( array_keys( $aParam ) ),
				$this->urlencode( array_values( $aParam ) )
			);

			$sReturn = "";
			foreach( $aParam as $sKey=>$sValue )
				$sReturn .= "&{$sKey}={$sValue}";
			return trim( !empty( $sAppendTo ) ? "{$sAppendTo}{$sReturn}" : $sReturn, "&" );
		}

		return "";
	}

	public function urlencode( $mValue )
	{
		if ( is_scalar( $mValue ) )
			return str_replace( Array( "+", "%7E" ), Array( " ", "~" ), rawurlencode( $mValue ) );
		else if ( is_array( $mValue ) )
			return array_map( Array( $this, "urlencode" ), $mValue );
		return "";
	}

	public function urldecode( $mValue )
	{
		if ( is_array( $mValue ) )
			return array_map( Array( $this, "urldecode" ), $mValue );
		return rawurldecode( $mValue );
	}
}