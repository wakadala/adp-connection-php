<?php

/*
Copyright © 2015-2016 ADP, LLC.

Licensed under the Apache License, Version 2.0 (the “License”);
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an “AS IS” BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
express or implied.  See the License for the specific language
governing permissions and limitations under the License.
*/
/**
 *
 *  This class acts as a factory to return static instances of any tools.
 *
 */
class adpapiUtilityFactory {

	/**
	* Array of utility class instances
	* @var array $utility
	*/
	protected static $utility = array();

	public function __construct() {

		// Do nothing.

	}

    /**
     * Utility Factory - returns a static instance of a utility class to be used anywhere
     *
     * @codeCoverageIgnore
     * @param string $name - Name of the utility instance to create
     * @return class
     */
	public static function getObject($name) {


		if (empty(self::$utility[$name])) {

			$classname = 'adpapiUtility' . ucfirst($name);

			self::$utility[$name] = new $classname;

		}

		return self::$utility[$name];

	}

}


/**
 *
 *	Logger Class.
 *
 *  This class allows logging of data.
 *
 */

 class adpapiUtilityLogger {

	/**
	* Logging - Status of logger.
	* @var string
	*/
 	public $logging;

	/**
	* Logmode - Configuration mode.
	* @var string
	*/
 	public $logmode;

	/**
	* Logfile - Name of the logfile to log to, if necessary.
	* @var string
	*/
 	public $logfile;

	/**
	* Constructor
	*/
 	public function __construct () {

		$this->logging = 1;


 	}

	/**
	* Write an entry to the log
	* @param string $line - The line of text for the log
	*/
 	public function write($line) {


 		// PHPUNIT doesnt like referring to class variables, so we transfer them to local vars

 		// @codeCoverageIgnoreStart
 		$logging = $this->logging;
 		$logmode = $this->logmode;
 		$logfile = $this->logfile;
 		// @codeCoverageIgnoreEnd

 		if ($logging == 1) {

	 		$iscli = (php_sapi_name() === 'cli' OR defined('STDIN'));	// Catch CLI and PHP-CGI conditions.

			$str = "[APILOG] ";

			if ($iscli || $logmode == 1) {

				$str = "[ " . date("Y-m-d H:i:s") . " ] - ";			// Add date and time if we're in our own file

			}

			$str .= $line;

			if ($iscli || $logmode == 1) {						// Writing to log file

				$str .= "\n";

				$handle = @fopen($logfile, "a+");
				@fwrite($handle, $str);
				@fclose($handle);

			}

			// Because PHPUNIT fails with elses
			if (!$iscli && $logmode != 1) {
				// @codeCoverageIgnoreStart
				error_log($str, 0);
				// @codeCoverageIgnoreEnd
			}

		}

		return;

 	}

 }

// Create and fill in settings from config.php

$logger = adpapiUtilityFactory::getObject('logger');

$logger->logging = $adp_logging;
$logger->logmode = $adp_logmode;
$logger->logfile = $adp_logfile;


/**
 *
 *	adpapiProduct - Abstract Class
 *
 *  This is the base abstract class for all of the api calls, with exception to the connection.  This is so
 *  we can have shared functionality in one place, instead of everywhere.
 *
 *
 *  This class is included in this file for convenience, since the connection is required for all other apis.
 *
 *  @codeCoverageIgnore
 */
abstract class adpapiProduct {

	/**
	* Connection Object
	* @var object
	*/
	protected $connection;

	/**
	* Local Logging Instance
	* @var object
	*/
	public $logger;

	/**
	 * Constructor.  Sets up the product helper.
	 *
	 * @param object $conn - The connection object.
	 */
	public function __construct($conn) {

		$this->connection = $conn;
		$this->logger = adpapiUtilityFactory::getObject("logger");

	}

}

/**
 *
 *	adpException Class
 *
 *  This is the extended exception class for adpapi errors.
 *
 *  This class is included in this file for convenience, since the connection is required for all other apis.
 *
 */

class adpException extends Exception
{

	/**
	 * Response from server
	 * @var string
	 */
	protected $response;

	/**
	 * HTTP Status Code
	 * @var integer
	 */
	protected $status;


    /**
     * Constructor
     *
     * @param string $message - Message for exception
     * @param integer $code - the HTTP status code, if any
     * @param object $previous - Previous exception, if any
     * @param mixed $response - The Response from the server
     */
    public function __construct($message, $code = 0, Exception $previous = null, $response) {

    	$this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Custom string representation of the exception
	 * @return string - String value of exception
	 */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}  ::  {$this->response}\n";
    }

    /**
     * Return response value
     * @return mixed
     */
    public function getResponse() {

    	return $this->response;

    }

    /**
     * Return http status
     * @return integer
     */
    public function getStatus() {

    	return $this->code;

    }


}
