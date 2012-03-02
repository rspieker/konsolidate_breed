<?php

class BreedUser extends CoreUser
{
	/**
	 *  The 'salt' to use in order to hash the password
	 *  @name    _salt
	 *  @type    string
	 *  @access  protected
	 */
	protected $_salt;
	/**
	 *  The number of 'salting' passes
	 *  @name    _salting
	 *  @type    int
	 *  @access  protected
	 */
	protected $_salting;


	public function __construct( $oParent )
	{
		parent::__construct( $oParent );

		$this->setSalt($this->get('/Config/User/passwordsalt'));
		$this->_salting = $this->get('/Config/User/passwordsalting', 3);
	}

	public function setSalt($salt)
	{
		$this->_salt = $salt;
	}

	/**
	 *  Create a user data record
	 *  @name    create
	 *  @type    method
	 *  @access  public
	 *  @param   integer   user id
	 *  @param   string    email address
	 *  @param   string    password [optional]
	 *  @param   bool      agree [optional]
	 *  @param   bool      opt in [optional]
	 *  @param   bool      track [optional]
	 *  @returns bool
	 *  @syntax  bool BreedUser->create( integer userid, string email [, string password [, bool agree [, bool optin [, bool track ] ] ] ] );
	 */
	public function create($email, $password=false, $userAgree=false, $userOptIn=false, $doTrack=true)
	{
		return parent::create($email, $this->_passwordHash($password), $userAgree, $userOptIn, $doTrack);
	}

	/**
	 *  Authenticate a user based on its credentials
	 *  @name    login
	 *  @type    method
	 *  @access  public
	 *  @param   string email address
	 *  @param   string password
	 *  @param   bool   autologin [default true]
	 *  @returns string usertracker code (or bool false on error)
	 *  @syntax  string BreedUser->login( string email, string password [, bool autologin ] );
	 */
	public function login($email, $password, $useAutoLogin=true)
	{
		return parent::login($email, $this->_passwordHash($password), $useAutoLogin);
	}

	/**
	 *  Create a hashed version of the password, so no actual passwords are stored in the database
	 *  @name    _passwordHash
	 *  @type    method
	 *  @access  protected
	 *  @param   string password
	 *  @param   string addition [default empty]
	 *  @returns string passwordhash
	 *  @syntax  string BreedUser->_passwordHash( string password [, string addition ] );
	 */
	protected function _passwordHash($password, $addition=null)
	{
		if ( $password !== false )
		{
			if (empty($this->_salt))
				$this->exception( "No proper salt defined for hashing passwords." );
			for ($i = 0; $i < $this->_salting; ++$i)
				$password = hash('sha256', $this->_salt . $password . $this->_salt . $addition);
		}
		return $password;
	}
}
