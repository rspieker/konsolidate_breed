<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedAuthenticationOAuth
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: Authentication/OAuth
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 *
 *  @name   BreedAuthenticationOAuth
 *  @type   class
 *  @date   2/27/11
 *  @author Rogier Spieker <rogier@konsolidate.net>
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
