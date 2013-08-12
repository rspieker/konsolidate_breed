<?php

/**
 *  Provide easy access to uploaded files
 *  @name     BreedRequestFile
 *  @type     class
 *  @package  Breed
 *  @author   Rogier Spieker <rogier@konfirm.net>
 */
class BreedRequestFile extends CoreRequestFile
{
	/**
	 *  Move the uploaded file to target destination
	 *  @name    move
	 *  @type    method
	 *  @access  public
	 *  @param   string  destination
	 *  @param   bool    safe name [optional, default true]
	 *  @returns void
	 *  @syntax  bool CoreRequestFile->move( string destination [, bool safename ] )
	 */
	public function move( $sDestination, $bSafeName=true )
	{
		if ( is_uploaded_file( $this->tmp_name ) )
		{
			if ( is_dir( realpath( $sDestination ) ) ) //  only directory provided, appending filename to it
			{
				$sDestination = realpath( $sDestination ) . "/" . ( $bSafeName ? $this->sanitizedname : $this->name );
			}
			else if ( !strstr( basename( $sDestination ), "." ) ) //  assuming a dot in every filename... possible weird side effects?
			{
				mkdir( $sDestination, 0777, true );
				$sDestination = realpath( $sDestination ) . "/" . ( $bSafeName ? $this->sanitizedname : $this->name );
			}

			if ( move_uploaded_file( $this->tmp_name, $sDestination ) )
			{
				unset( $this->_property[ "tmp_name" ] );
				$this->location = $sDestination;
				return true;
			}
		}
		return false;
	}

	/**
	 *  Implicit set of properties
	 *  @name    __set
	 *  @type    method
	 *  @access  public
	 *  @param   string  property
	 *  @param   mixed   value
	 *  @returns void
	 *  @syntax  bool CoreRequestFile->{string property} = mixed value;
	 *  @note    some additional properties are automaticalaly added when certain properties are set.
	 *           - 'error' also sets 'message', a string containing a more helpful error message.
	 *           - 'name' also set 'sanitizedname', a cleaned up (suggested) name for the file.
	 *           - 'tmp_name' also sets 'md5', the MD5 checksum of the file.
	 *           - 'size' also sets 'filesize', a human readable representation of the file size.
	 */
	public function __set( $sProperty, $mValue )
	{
		if ( !empty( $mValue ) || $sProperty == "error" )
		{
			parent::__set( $sProperty, $mValue );
			switch( $sProperty )
			{
				case "error":
					$this->_property[ "message" ] = $this->_getErrorMessage( $mValue );
					$this->_property[ "success" ] = $mValue == UPLOAD_ERR_OK;
					break;
				case "name":
					$this->_property[ "sanitizedname" ] = $this->call( "/Resource/suggestSanitizedPath", $mValue );
					break;
				case "tmp_name":
					$this->_property[ "md5" ] = md5_file( $mValue );
					break;
				case "size":
					$this->_property[ "filesize" ] = $this->_bytesToLargestUnit( $mValue );
					break;
			}
		}
	}
}