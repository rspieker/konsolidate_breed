<?php

/*
 *            ________ ___
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /
 *    \  /   /\   \  /    \       Class:  BreedAuthenticationOAuthSignatureHMAC
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: /Authentication/OAuth/Signature/HMAC
 *       \___\/  \___\/  \/
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/
 *
 *  @name   BreedAuthenticationOAuthSignatureHMAC
 *  @type   class
 *  @date   2/27/11
 *  @author Rogier Spieker <rogier@konsolidate.net>
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
