<?php

/**
 *  @name     BreedUserTracker
 *  @type     class
 *  @package  Breed
 *  @author   Rogier Spieker <rogier@konfirm.net>
 */
class BreedUserTracker extends CoreUserTracker
{
	/**
	 *  Create a unique code
	 *  @name    createCode
	 *  @type    method
	 *  @access  public
	 *  @returns bool
	 *  @syntax  bool CoreUserTracker->createCode();
	 */
	public function createCode()
	{
		return $this->call('/Key/uuid');
	}
}
