<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedResourceAlias
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: Resource/Alias
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev$
 *          \___    ___\/         $Author$
 *              \   \  /          $Date$
 *               \___\/           
 */


/**
 *  Resource aliases
 *  Determine and resolve the true resource
 *  @name    BreedResourceAlias
 *  @type    class
 *  @package Konsolidate
 *  @author  Rogier Spieker <rogier@konsolidate.net>
 */
 class BreedResourceAlias extends Konsolidate
 {
 	public function resolve( $sPath, $bRedirect=true )
 	{
 		$sQuery  = "SELECT rsc.rscpath AS path,
						   alt.rscpath AS alias,
						   IF ( ral.ralredirect IS NULL, 0, ral.ralredirect ) AS redirect
					  FROM resource rsc
					  LEFT OUTER JOIN resourcealias ral ON ral.ralenabled AND ral.rscid=rsc.rscid
					  LEFT OUTER JOIN resource alt ON alt.rscenabled=true AND alt.rscid=ral.rscid2
					 WHERE rsc.rscenabled=true
					   AND rsc.rscpath=" . $this->call( "/DB/quote", $sPath );
		$oResult = $this->call( "/DB/query", $sQuery );
		if ( is_object( $oResult ) && $oResult->errno <= 0 && $oResult->rows > 0 )
		{
			$oRecord = $oResult->next();
			if ( !empty( $oRecord->alias ) )
			{
				$sPath = $oRecord->alias;
				if ( (bool) $oRecord->redirect && $bRedirect )
					$this->call( "/Tool/redirect", $sPath );
			}
		}
		return $sPath;
 	}

	public function resolveMultiple( $aPath, $bRedirect=true )
	{
 		if ( !is_array( $aPath ) )
 			$aPath = Array( $aPath );

			$sReturn = $aPath[ 0 ];

			for ( $i = 0; $i < count( $aPath ); ++$i )
				$aPath[ $i ] = $this->call( "/DB/quote", $aPath[ $i ] );

 		$sQuery  = "SELECT rsc.rscpath AS path,
						   alt.rscpath AS alias,
						   IF ( ral.ralredirect IS NULL, 0, ral.ralredirect ) AS redirect
					  FROM resource rsc
					  LEFT OUTER JOIN resourcealias ral ON ral.ralenabled AND ral.rscid=rsc.rscid
					  LEFT OUTER JOIN resource alt ON alt.rscenabled=true AND alt.rscid=ral.rscid2
					 WHERE rsc.rscenabled=true
					   AND rsc.rscpath IN ( " . implode( ",", $aPath ) . " )
					 ORDER BY ( IF( alt.rscpath IS NULL, 0, 1 ) - LENGTH( rsc.rscpath ) ) ASC";
		$oResult = $this->call( "/DB/query", $sQuery );
		if ( is_object( $oResult ) && $oResult->errno <= 0 && $oResult->rows > 0 )
		{
			$oRecord = $oResult->next();
			if ( !empty( $oRecord->alias ) )
			{
				$sReturn = $oRecord->alias;
				if ( (bool) $oRecord->redirect && $bRedirect )
					$this->call( "/Tool/redirect", $sReturn );
			}
			else
			{
				return $oRecord->path;
			}
		}
		return $sReturn;
	}
}
