<?php

class BreedDBMySQLiInfo extends Konsolidate
{
	public function process( $oConnection, $bExtendInfo=false, $aAppendInfo=null )
	{
		$this->info = $oConnection->info;
		$aInfo      = $this->_parseData( $this->info );

		if ( is_array( $aAppendInfo ) )
			$aInfo = array_merge( $aInfo, $aAppendInfo );

		if ( $bExtendInfo )
		{
			$this->stat = $oConnection->stat();
			$aInfo = array_merge( $aInfo, $this->_parseData( $this->stat ) );
		}

		foreach( $aInfo as $sKey=>$mValue )
			$this->{$sKey} = $mValue;
	}

	public function collect()
	{
		$aArg = func_get_args();
		if ( !(bool) count( $aArg ) )
			$aArg = Array( "Variable", "Status", "Table" );
		foreach( $aArg as $sModule )
			$this->register( $sModule );
	}

	protected function _parseData( $sData )
	{
		if ( !empty( $sData ) && (bool) preg_match_all( "/([a-z_]+)\:([0-9\.]+),*/", preg_replace( Array( "/\s\s/", "/\:\s/", "/\s/" ), Array( ",", ":", "_" ), strtolower( $sData ) ), $aMatch ) && count( $aMatch ) == 3 )
			return array_combine( $aMatch[ 1 ], $aMatch[ 2 ] );
		return Array();
	}

	/**
	 *  Automatically populate child modules (Variables, Table and Status if requested)
	 *  @name    register
	 *  @type    method
	 *  @access  public
	 *  @param   string module/connection
	 *  @returns Object
	 *  @note    this method is an override to Konsolidates default behaviour
	 */
	public function register( $sModule )
	{
		$oModule = parent::register( $sModule );
		switch( strtolower( $sModule ) )
		{
			case "variable":
				$sQuery  = "SHOW VARIABLES";
				$oResult = $this->call( "../query", $sQuery );
				if ( is_object( $oResult ) && $oResult->errno <= 0 && (bool) $oResult->rows )
					while( $oRecord = $oResult->next() )
						$oModule->set( strtolower( $oRecord->Variable_name ), $oRecord->Value );
				break;
			case "status":
				$sQuery  = "SHOW GLOBAL STATUS";
				$oResult = $this->call( "../query", $sQuery );
				if ( is_object( $oResult ) && $oResult->errno <= 0 && (bool) $oResult->rows )
					while( $oRecord = $oResult->next() )
						$oModule->set( "Global/" . strtolower( $oRecord->Variable_name ), $oRecord->Value );
	
				$sQuery  = "SHOW SESSION STATUS";
				$oResult = $this->call( "../query", $sQuery );
				if ( is_object( $oResult ) && $oResult->errno <= 0 && (bool) $oResult->rows )
					while( $oRecord = $oResult->next() )
						$oModule->set( "Session/" . strtolower( $oRecord->Variable_name ), $oRecord->Value );
				break;
			case "table":
				$sQuery  = "SHOW TABLE STATUS";
				$oResult = $this->call( "../query", $sQuery );
				if ( is_object( $oResult ) && $oResult->errno <= 0 && (bool) $oResult->rows )
					while( $oRecord = $oResult->next() )
					{
						$sName = $oRecord->Name;
						foreach( $oRecord as $sKey=>$sValue )
							$oModule->set( "{$sName}/" . strToLower( $sKey ), $sValue );
					}
				break;
		}
		return $oModule;
	}
}
