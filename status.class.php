<?php

/**
 *  Provide HTTP statuses
 *  @name    BreedStatus
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedStatus extends Konsolidate
{
	public function send($status, $message='', $useHTTP=true)
	{
		if (!headers_sent())
		{
			header(($useHTTP ? 'HTTP/1.0' : 'Status:') . ' ' . $status . ' ' . (!empty($message) ? $message : $this->getMessage($status)));
			return true;
		}
		return false;
	}

	public function getMessage($status)
	{
		$message = 'Unknown';

		switch($status)
		{
			/*  1xx - INFORMATIONAL  */
			case 100:
				$message = 'Continue';
				break;
			case 101:
				$message = 'Switching Protocols';
				break;


			/*  2xx - SUCCESSFUL  */
			case 200:
				$message = 'OK';
				break;
			case 201:
				$message = 'Created';
				break;
			case 202:
				$message = 'Accepted';
				break;
			case 203:
				$message = 'Non-Authoritative Information';
				break;
			case 204:
				$message = 'No Content';
				break;
			case 205:
				$message = 'Reset Content';
				break;
			case 206:
				$message = 'Partial Content';
				break;
			case 207:
				$message = 'Multi-Status';
				break;


			/*  3xx - REDIRECTION  */
			case 300:
				$message = 'Multiple Choices';
				break;
			case 301:
				$message = 'Moved Permanently';
				break;
			case 302:
				$message = 'Found';
				break;
			case 303:
				$message = 'See Other';
				break;
			case 304:
				$message = 'Not Modified';
				break;
			case 305:
				$message = 'Use Proxy';
				break;
			case 306:
				$message = '(Reserved)';
			case 307:
				$message = 'Temporary Redirect';
				break;


			/*  4xx - CLIENT ERROR  */
			case 400:
				$message = 'Bad Request';
				break;
			case 401:
				$message = 'Unauthorized';
				break;
			case 402:
				$message = 'Payment Required';
				break;
			case 403:
				$message = 'Forbidden';
				break;
			case 404:
				$message = 'Not Found';
				break;
			case 405:
				$message = 'Method Not Allowed';
				break;
			case 406:
				$message = 'Not Acceptable';
				break;
			case 407:
				$message = 'Proxy Authentication';
				break;
			case 408:
				$message = 'Request Timeout';
				break;
			case 409:
				$message = 'Conflict';
				break;
			case 410:
				$message = 'Gone';
				break;
			case 411:
				$message = 'Length Required';
				break;
			case 412:
				$message = 'Precondition Failed';
				break;
			case 413:
				$message = 'Request Entity Too Large';
				break;
			case 414:
				$message = 'Request-URI Too Long';
				break;
			case 415:
				$message = 'Unsupported Media Type';
				break;
			case 416:
				$message = 'Requested Range Not Satisfiable';
				break;
			case 417:
				$message = 'Expectation Failed';
				break;
			case 422:
				$message = 'Unprocessable Entity';
				break;
			case 423:
				$message = 'Locked';
				break;
			case 424:
				$message = 'Failed Dependency';
				break;


			/*  5xx - SERVER ERROR  */
			case 500:
				$message = 'Internal Server Error';
				break;
			case 501:
				$message = 'Not Implemented';
				break;
			case 502:
				$message = 'Bad Gateway';
				break;
			case 503:
				$message = 'Service Unavailable';
				break;
			case 504:
				$message = 'Gateway Timeout';
				break;
			case 505:
				$message = 'HTTP Version Not Supported';
				break;
			case 507:
				$message = 'Insufficient Storage';
				break;
		}

		return $message;
	}
}