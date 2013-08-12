<?php

/*
 *  @name    BreedAuthenticationOAuthToken
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedAuthenticationOAuthToken extends Konsolidate
{
	public $key;
	public $secret;

	public function initialize( $sKey=null, $sSecret=null )
	{
		$this->key    = $sKey;
		$this->_ecret = $sSecret;
	}

	public function __toString()
	{
		return $this->key;
	}
}
