<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedEncryptionHMAC
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: /Encryption/HMAC
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 *
 *  @name   BreedEncryptionHMAC
 *  @type   class
 *  @date   2/27/11
 *  @author Rogier Spieker <rogier@konsolidate.net>
 */

/*
 *  Encryption/HMAC is fully based on the PEAR package Crypt_HMAC by Derick Rethans and Matthew Fonda
 */
class BreedEncryptionHMAC extends Konsolidate
{
	const DEFAULT_HASH_METHOD="md5";

	protected $_hashpack = Array(
		"md5"=>"H32",
		"sha1"=>"H40"
	);

	public function hash( $sKey, $sData, $sHashMethod=null )
	{
		if ( is_null( $sHashMethod ) )
			$sHashMethod = self::DEFAULT_HASH_METHOD;

		//  We can save ourself a bit of effort if the hash extension is available
		if ( extension_loaded( "hash" ) )
			return hash_hmac( $sHashMethod, $sData, $sKey, true );

		$sKey        = $this->_createHashKey( $sKey, $sHashMethod );
		$sPackMethod = $this->_getPackMethod( $sHashMethod );
		return pack( $sPackMethod, $sHashMethod( $this->_createPadded( $sKey, false ) . pack( $sPackMethod, $sHashMethod( $this->_createPadded( $sKey, true ) . $sData ) ) ) );
	}

	protected function _createPadded( $sHashKey, $bInnerPadded=true )
	{
		return ( substr( $sHashKey, 0, 64 ) ^ str_repeat( chr( $bInnerPadded ? 0x36 : 0x5C ), 64 ) );
	}

	protected function _createHashKey( $sKey, $sHashMethod=null )
	{
		if ( strlen( $sKey ) > 64 )
		{
			if ( is_null( $sHashMethod ) )
				$sHashMethod = self::DEFAULT_HASH_METHOD;
			$sPackMethod = $this->_getPackMethod( $sHashMethod );
			$sKey        =  pack( $sPackMethod, $sHashMethod( $sKey ) );
		}
		if ( strlen( $sKey ) < 64 )
		$sKey = str_pad( $sKey, 64, chr( 0 ) );

		return $sKey;
	}

	protected function _getPackMethod( $sHashMethod )
	{
		if ( array_key_exists( $sHashMethod, $this->_hashpack ) )
			return $this->_hashpack[ $sHashMethod ];
		$this->exception( "Unsupported Hash Function '{$mValue}'" );
	}
}
