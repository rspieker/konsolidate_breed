<?php

/*
 *  @name    BreedAuthenticationOAuthSignatureHMAC
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedAuthenticationOAuthSignatureHMAC extends Konsolidate
{
	public function identify( $sHashMethod="sha1" )
	{
		return "HMAC-" . strToUpper( $sHashMethod );
	}

	public function create( $sBase, $sSecret, $sToken=null, $sHashMethod="sha1" )
	{
		$sKey = $sSecret . "&" . ( empty( $sToken ) ? "" : $sToken );

		return base64_encode(
			function_exists( "hash_hmac" )
					? hash_hmac( $sHashMethod, $sBase, $sKey, true )
					: $this->call( "/Encryption/HMAC/hash", $sKey, $sRequest, $sHashMethod )
		);
	}
}