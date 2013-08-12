<?php

/*
 *  @name    BreedAuthenticationOAuth
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedAuthenticationOAuth extends Konsolidate
{
	const OAUTH_VERSION="1.0";


	public function __construct( $oParent )
	{
		parent::__construct( $oParent );

		$this->version = self::OAUTH_VERSION;
	}
}