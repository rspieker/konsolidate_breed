<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedAuthenticationOAuthToken
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: /Authentication/OAuth/Token
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 *
 *  @name   BreedAuthenticationOAuthToken
 *  @type   class
 *  @date   2/28/11
 *  @author Rogier Spieker <rogier@konsolidate.net>
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
