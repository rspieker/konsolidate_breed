<?php

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
