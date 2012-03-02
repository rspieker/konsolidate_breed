<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedValidate
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: Validate
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 */


/**
 *  Basic validation
 *  @name    BreedValidate
 *  @type    class
 *  @package Konsolidate
 *  @author  Rogier Spieker <rogier@konsolidate.net>
 */
class BreedValidate extends CoreValidate
{
	/**
	 *  does the variable contain a value
	 *  @name    isFilled
	 *  @type    method
	 *  @access  public
	 *  @param   mixed value
	 *  @returns bool
	 *  @syntax  Object->isFilled( mixed value );
	 */
	function isFilled( $mValue )
	{
		return ( !preg_match( "/^$/", $mValue ) );
	}

	/**
	 *  does the value represent a possible e-mail address
	 *  @name    isEmail
	 *  @type    method
	 *  @access  public
	 *  @param   mixed value
	 *  @returns bool
	 *  @syntax  Object->isEmail( mixed value );
	 *  @note    This method does NOT verify the actual existing of the e-mail address, it merely verifies that it complies to common e-mail addresses
	 */
	function isEmail( $mValue )
	{
		return preg_match( "/^[_a-z0-9-]+([a-z0-9\.\+_-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}|.info)$/i", $mValue );
	}
}
