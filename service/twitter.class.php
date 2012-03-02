<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedServiceTwitter
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: /Service/Twitter
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 *
 *  @name   BreedServiceTwitter
 *  @type   class
 *  @date   2/28/11
 *  @author Rogier Spieker <rogier@konsolidate.net>
 */

class BreedServiceTwitter extends Konsolidate
{
	protected $_basepath = "https://api.twitter.com/oauth";
	protected $_consumer;
	protected $_token;


	public function __construct( $oParent )
	{
		parent::__construct( $oParent );

		session_start();
		$this->_consumer = $this->instance( "/Authentication/OAuth/Consumer" );
		$this->_consumer->initialize(
			"__YOUR_APP_KEY__",
			"__YOUR_APP_SECRET__"
		);

		$this->_token = $this->_getAccessTokenFromSession();
	}

	public function test( $aParam=null )
	{
		if ( $this->_token || $this->_obtainAccessToken( $this->_consumer ) )
		{
//				return $this->call( "/Authentication/OAuth/Request/getResponseData", "{$this->_basepath}/echo_api.php", $aParam, $this->_consumer, $this->_token, "GET" );
		}
		return false;
	}

	protected function _obtainAccessToken( $oConsumer )
	{
		$oToken = $this->_processResponse( $this->call( "/Authentication/OAuth/Request/getResponseData", "{$this->_basepath}/request_token", null, $oConsumer, null, "GET" ) );
		if ( $oToken )
		{
			$oToken = (object) Array(
				"key"=>$oToken->oauth_token,
				"secret"=>$oToken->oauth_token_secret
			);
//var_dump( $this->call( "/Authentication/OAuth/Request/getResponseData", "{$this->_basepath}/access_token", null, $oConsumer, $oToken, "GET" ) );
//exit;
			$oAccess = $this->_processResponse( $this->call( "/Authentication/OAuth/Request/getResponseData", "{$this->_basepath}/access_token", null, $oConsumer, $oToken, "GET" ) );
			if ( $oAccess )
			{
//var_dump( $oAccess );
//exit;
				$this->_token = (object) Array(
					"key"=>$oAccess->oauth_token,
					"secret"=>$oAccess->oauth_token_secret
				);
				var_dump( $this->_token );
				return true;
			}
		}
		return false;
	}

	protected function _processResponse( $sResponse )
	{
		if ( $sResponse )
		{
			$aReturn = Array();
			$aPair   = explode( "&", $sResponse );
			foreach( $aPair as $sPair )
			{
				list( $sKey, $sValue ) = explode( "=", $sPair );
				$aReturn[ $sKey ]      = $sValue;
			}
			return (object) $aReturn;
		}

		return $sResponse;
	}

	protected function _getAccessTokenFromSession()
	{
		$sClassName = get_class( $this );
		if ( isset( $_SESSION ) && isset( $_SESSION[ $sClassName ] ) && isset( $_SESSION[ $sClassName ][ "access" ] ) && isset( $_SESSION[ $sClassName ][ "access" ][ (string) $this->_consumer ] ) )
			return (object) $_SESSION[ $sClassName ][ (string) $this->_consumer ];
		return false;
	}
}
