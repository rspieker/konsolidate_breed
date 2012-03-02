<?php

/*
 *            ________ ___        
 *           /   /   /\  /\       Konsolidate
 *      ____/   /___/  \/  \      
 *     /           /\      /      http://www.konsolidate.net
 *    /___     ___/  \    /       
 *    \  /   /\   \  /    \       Class:  BreedSystemFileMIME
 *     \/___/  \___\/      \      Tier:   Breed
 *      \   \  /\   \  /\  /      Module: System/File/MIME
 *       \___\/  \___\/  \/       
 *         \          \  /        $Rev: 94 $
 *          \___    ___\/         $Author: rogier $
 *              \   \  /          $Date: 2008-10-15 11:08:59 +0200 (Wed, 15 Oct 2008) $
 *               \___\/           
 */

/**
 *  MIME Detection/Guessing
 *  @name    BreedSystemFileMIME
 *  @type    class
 *  @package Konsolidate
 *  @author  Rogier Spieker <rogier@konsolidate.net>
 */
class BreedSystemFileMIME extends Konsolidate
{
	/**
	 *  Try to determine the MIME type using an (somewhat) educated guess based on the file extension
	 *  @name    _determineTypeByExtension
	 *  @type    method
	 *  @access  protected
	 *  @param   string filename
	 *  @returns string MIME
	 *  @syntax  string BreedSystemFileMIME->_determineTypeByExtension( string filename )
	 */
	protected function _determineTypeByExtension( $sFile )
	{
		$aFilePart  = explode( ".", $sFile );
	    $sExtension = array_pop( $aFilePart );
		switch( strToLower( $sExtension ) )
		{
			//  Common image types
			case "ai":    case "eps":
			case "ps":
				return "application/postscript";
			case "bmp":
				return "image/bmp";
			case "gif":
				return "image/gif";
			case "jpe":   case "jpg":
			case "jpeg":
				return "image/jpeg";
			case "png":
				return "image/png";

			//  Common audio types
			case "aifc":  case "aiff":
			case "aif":
				return "audio/aiff";
			case "mid":   case "midi":
				return "audio/midi";
			case "mod":
				return "audio/mod";
			case "mp2":
				return "audio/mpeg";
			case "mp3":
				return "audio/mpeg3";
			case "wav":
				return "audio/wav";

			//  Common video types
			case "avi":
				return "video/avi";
			case "mov":  case "qt":
				return "video/quicktime";
			case "mpe":  case "mpg":
			case "mpeg":
				return "video/mpeg";

			//  Common text types
			case "css":
				return "text/css";
			case "htm":   case "html":
			case "htmls": case "htx":
				return "text/html";
			case "conf":  case "log":
			case "text":  case "txt":
			case "php":
				return "text/plain";
			case "js":
				return "application/x-javascript";
			case "rtf":
				return "text/richtext";
			case "xml":
				return "text/xml";
			case "xsl":   case "xslt":
				return "text/xslt";

			//  Other commonly used types
			case "json":
				return "application/json";
			case "dcr":
				return "application/x-director";
			case "doc":  case "dot":
			case "word":
				return "application/msword";
			case "gz":   case "gzip":
				return "application/x-gzip";
			case "latex":
				return "application/x-latex";
			case "pdf":
				return "application/pdf";
			case "pps":  case "ppt":
				return "application/mspowerpoint";
			case "swf":
				return "application/x-shockwave-flash";
			case "wp":   case "wp5":
			case "wp6":  case "wpd":
				return "application/wordperfect";
			case "xls":
				return "application/excel";
			case "zip":
				return "application/zip";
			default:
				return "application/octet-stream";
		}
	}
}
