<?php

/*
 *  @name    BreedAuthenticationOAuthConsumer
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedAuthenticationOAuthConsumer extends Konsolidate
{
	public $key;
	public $secret;

	public function initialize( $sKey=null, $sSecret=null )
	{
		$this->key    = $sKey;
		$this->secret = $sSecret;
	}

	public function __toString()
	{
		return $this->key;
	}
}