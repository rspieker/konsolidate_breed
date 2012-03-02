<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedAuthenticationOAuthConsumer
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: /Authentication/OAuth/Consumer
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 *
 *  @name   BreedAuthenticationOAuthConsumer
 *  @type   class
 *  @date   2/28/11
 *  @author Rogier Spieker <rogier@konsolidate.net>
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

