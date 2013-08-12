<?php

/**
 *  @name     BreedTool
 *  @type     class
 *  @package  Breed
 *  @author   Rogier Spieker <rogier@konfirm.net>
 */
class BreedTool extends CoreTool
{
	static public function documentRoot()
	{
		if (isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['SCRIPT_NAME']))
			return substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME']));
		return false;
	}

	static public function documentPath()
	{
		if (isset($_SERVER['SCRIPT_NAME']))
			return self::documentRoot() . dirname($_SERVER['SCRIPT_NAME']);
		return false;
	}
}