<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedResourcePrivilege
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: Resource/Privilege
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 */


/**
 *  Resources
 *  @name    BreedResourcePrivilege
 *  @type    class
 *  @package Konsolidate
 *  @author  Rogier Spieker <rogier@konsolidate.net>
 */
class BreedResourcePrivilege extends Konsolidate
{
	const PRIVILEGE_READ   = "read";
	const PRIVILEGE_WRITE  = "write";
	const PRIVILEGE_ACCESS = "access";

	protected $_resource;

	public function __construct( $oParent )
	{
		parent::__construct( $oParent );
		$this->_resource = Array();
	}

	/**
	 *  Create a resource (if it doesn't exist) and create the provided privilege(s) for the given types
	 *  @name    create
	 *  @type    method
	 *  @access  public
	 *  @param   string resource
	 *  @param   int    user id (optional, default null)
	 *  @param   int    group id (optional, default null)
	 *  @param   string privilege (optional, default 755)
	 *  @returns bool   (null if no explicit permission is set)
	 *  @syntax  (bool) Object->create( string resource [, int userid [, int groupid [, string privilege ] ] ] )
	 *  @note    The method allows full control over which type(s) will be affected by the privilege by ommiting
	 *           the user and/or group or by shortening the privilege string.
	 *           If user id and/or group id is ommited, no privilege will be created for those type, if string privilege
	 *           is shorter than 3 no 'other' type record will be created.
	 *           e.g. Object->create( "/my/resource", 1, null, "755" )    - creates 'user' and 'other' privileges
	 *                Object->create( "/my/resource", null, 1, "755" )    - creates 'group' and 'other' privileges
	 *                Object->create( "/my/resource", null, null, "755"   - creates 'other' privileges
	 *                Object->create( "/my/resource", 1, null, "7" )      - creates only 'user' privileges
	 *                Object->create( "/my/resource", null, 1, "7" )      - does nothing
	 *                Object->create( "/my/resource", null, 1, "75" )     - creates only 'group' privileges
	 *                Object->create( "/my/resource", null, null, "75" )  - does nothing
	 *                Object->create( "/my/resource", null, null, "755" ) - creates only 'other' privileges
	 */
	public function create( $sResource, $nUser=null, $nGroup=null, $sPrivilege="755" )
	{
		$nResource = $this->call( "../create", $sResource );
		if ( $nResource !== false )
		{
			$bResult = true;
			for ( $i = 0; $i < strlen( $sPrivilege ); ++$i )
				switch( $i )
				{
					case 0: //  User
						if ( !is_null( $nUser ) )
							$bResult &= $this->_create( $nResource, (int) substr( $sPrivilege, $i, 1 ), $nUser );
						break;
					case 1: //  Group
						if ( !is_null( $nGroup ) )
							$bResult &= $this->_create( $nResource, (int) substr( $sPrivilege, $i, 1 ), null, $nGroup );
						break;
					case 2: //  Others
						$bResult &= $this->_create( $nResource, (int) substr( $sPrivilege, $i, 1 ) );
						break;
				}
			return $bResult;
		}
		return false;
	}

	/**
	 *  Does the current user have explicit read permission
	 *  @name    allowRead
	 *  @type    method
	 *  @access  public
	 *  @param   string resource
	 *  @returns bool   (null if no explicit permission is set)
	 *  @syntax  (bool) Object->allowRead( string resource )
	 */
	public function allowRead( $sResource, $mDefault=null )
	{
		$mReturn = $this->hasPrivilege( $sResource, self::PRIVILEGE_READ );
		return !is_null( $mReturn ) ? $mReturn : $mDefault;
	}

	/**
	 *  Does the current user have explicit write permission
	 *  @name    allowWrite
	 *  @type    method
	 *  @access  public
	 *  @param   string resource
	 *  @returns bool   (null if no explicit permission is set)
	 *  @syntax  (bool) Object->allowWrite( string resource )
	 */
	public function allowWrite( $sResource, $mDefault=null )
	{
		$mReturn = $this->hasPrivilege( $sResource, self::PRIVILEGE_WRITE );
		return !is_null( $mReturn ) ? $mReturn : $mDefault;
	}

	/**
	 *  Does the current user have explicit access permission
	 *  @name    allowAccess
	 *  @type    method
	 *  @access  public
	 *  @param   string resource
	 *  @returns bool   (null if no explicit permission is set)
	 *  @syntax  (bool) Object->allowAccess( string resource )
	 */
	public function allowAccess( $sResource, $mDefault=null )
	{
		$mReturn = $this->hasPrivilege( $sResource, self::PRIVILEGE_ACCESS );
		return !is_null( $mReturn ) ? $mReturn : $mDefault;
	}

	/**
	 *  Does the current user have (explicit) read permission
	 *  @name    hasPrivilege
	 *  @type    method
	 *  @access  public
	 *  @param   string resource
	 *  @param   string privilege (one of: 'read', 'write' or 'access')
	 *  @returns bool   (null if no explicit permission is set)
	 *  @syntax  (bool) Object->hasPrivilege( string resource, string privilege )
	 */
	public function hasPrivilege( $sResource, $sPrivilege )
	{
		$aPrivilege = $this->_loadPrivilege( $sResource );
		if ( $aPrivilege !== false )
			switch( strToLower( $sPrivilege ) )
			{
				case self::PRIVILEGE_READ:
					return (bool) $aPrivilege->{self::PRIVILEGE_READ};
				case self::PRIVILEGE_WRITE:
					return (bool) $aPrivilege->{self::PRIVILEGE_WRITE};
				case self::PRIVILEGE_ACCESS:
					return (bool) $aPrivilege->{self::PRIVILEGE_ACCESS};
			}
		return null;
	}

	/**
	 *  Create a privilege record
	 *  @name    _create
	 *  @type    method
	 *  @access  protected
	 *  @param   int    resource id
	 *  @param   int    privilege (4=read + 2=write + 1=access, 7 being all, 0 being none)
	 *  @param   int    user id (optional)
	 *  @param   int    group id (optional)
	 *  @returns bool
	 *  @syntax  (bool) Object->_create( int resourceid, int privilege [, int userid [, int groupid ] ] )
	 */
	protected function _create( $nResource, $nPrivilege, $nUser=null, $nGroup=null )
	{
		$sQuery  = "INSERT INTO resourceprivilege ( rscid, rpvprivilege, usrid, ugpid, rpvcreatedts )
					(
						{$nResource},
						{$nPrivilege},
						" . ( !is_null( $nUser ) ? $nUser : "NULL" ) . ",
						" . ( !is_null( $nGroup ) ? $nGroup : "NULL" ) . ",
						NOW()
					)
					ON DUPLICATE KEY
					UPDATE rpvprivilege=VALUES(rpvprivilege)";
		$oResult = $this->call( "/DB/query", $sQuery );
		if ( is_object( $oResult ) && $oResult->errno <= 0 )
			return true;
		return false;
	}

	/**
	 *  Load the available privileges for the resource from the database, taking into account the stacking order
	 *  of privileges (as per UNIX-defaults), user -> group -> other, meaning that privileges are returned in this order
	 *  cancelling the least implicit privilege(s).
	 *  @name    _loadPrivilege
	 *  @type    method
	 *  @access  protected
	 *  @param   int    resource id
	 *  @param   bool   force query (default false, aka use cache)
	 *  @returns object
	 *  @syntax  (bool) Object->_loadPrivilege( string resource [, int userid ] )
	 *  @note    The returned object is a PHP builtin stdClass, containing the following properties:
	 *           - read   (bool)
	 *           - write  (bool)
	 *           - access (bool)
	 *           - type   (int)  0=user, 1=group, 2=other
	 */
	protected function _loadPrivilege( $sResource, $bForce=false )
	{
		if ( $bForce || !array_key_exists( $sResource, $this->_resource ) )
		{
			//  The type column in the following query represents: 0 = user, 1 = group, 2 = others
			//  It indicates the stack order of the privilege (conforming to UNIX standards)
			//  The resultset can be limited to 1 because as per UNIX standards, always the most explicit privilege set is leading
			//  meaning we can trust the ordering by type ( user, group, other ) to determine the correct privilege
			$sQuery  = "SELECT IF( rpv.usrid IS NOT NULL, 0, IF( ugp.usrid IS NOT NULL, 1, 2 ) ) AS `type`,
						       MAX( IF( rpv.rpvprivilege & 4, 1, 0 ) ) AS `" . self::PRIVILEGE_READ ."`,
						       MAX( IF( rpv.rpvprivilege & 2, 1, 0 ) ) AS `" . self::PRIVILEGE_WRITE . "`,
						       MAX( IF( rpv.rpvprivilege & 1, 1, 0 ) ) AS `" . self::PRIVILEGE_ACCESS . "`
						  FROM resourceprivilege rpv
						 INNER JOIN resource rsc ON rsc.rscid=rpv.rscid AND rsc.rscpath=". $this->call( "/DB/quote", $sResource ) . "
						  LEFT OUTER JOIN usergroup ugp ON ugp.gnmid=rpv.gnmid
						 WHERE ( rpv.usrid IS NULL AND rpv.gnmid IS NULL )
						    OR ugp.usrid='" . $this->get( "/User/id" ) . "'
						    OR rpv.usrid='" . $this->get( "/User/id" ) . "'
						    GROUP BY type, rsc.rscpath
						 ORDER BY type
						 LIMIT 1";
			$oResult = $this->call( "/DB/query", $sQuery );
			if ( is_object( $oResult ) && $oResult->errno <= 0 && $oResult->rows > 0 )
				$this->_resource[ $sResource ] = $oResult->next();
		}
		return array_key_exists( $sResource, $this->_resource ) ? $this->_resource[ $sResource ] : false;
	}
}
