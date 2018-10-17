<?php

namespace App;

class Bootstrap {

	private static $action_mapper = [
		'initAllCategory' => ['Category', 'getAll'],
		'initProductByManu' => ['Product', 'getByManufacturer'],
		'initProductByManuCat' => ['Product', 'getByManufacturerCategory'],
		'initProductByManuPL' => ['Product', 'getByManufacturerProductLine'],
		'initProductByPL' => ['Product', 'getByProductLine'],
		// 'initProduct' => ['Product', 'get'],
		'dumpTxCloudJson' => ['Dumper', 'txCloudJson'],
		'dumpLogo' => ['Dumper', 'logo'],
	];

	public function __construct() {
		// Sets a user-defined exception handler function
		set_exception_handler([$this, 'exceptionHandler']);
		// Installs a signal handler
		Core\SignalHandler::init([__CLASS__, 'terminate']);
		// Gets options from the command line argument list
		$options = getopt('f::h::', ['fresh::', 'help::']);
		if(isset($options['h']) || isset($options['help'])) {
			self::usage();
		}
		for($i = 1; $i < $GLOBALS['argc']; $i++) {
			if(substr($GLOBALS['argv'][$i], 0, 1) == '-') continue;
			$action = $GLOBALS['argv'][$i++];
			break;
		}
		if(!isset($action)) {
			trigger_error('Missing action.'.PHP_EOL, E_USER_NOTICE);
			self::usage();
		}
		if(!isset(self::$action_mapper[$action])) {
			trigger_error('Invalid action - '.$action.PHP_EOL, E_USER_ERROR);
		}
		$is_arg = true;
		$args = $params = [];
		if(isset($options['f']) || isset($options['fresh'])) {
			$params['fresh_db'] = true;
		}
		for(; $i < $GLOBALS['argc']; $i++) {
			if($GLOBALS['argv'][$i] == '--') {
				$is_arg = false;
				continue;
			}
			if($is_arg) {
				$args[] = $GLOBALS['argv'][$i];
			}
			else {
				list($key, $value) = explode('=', $GLOBALS['argv'][$i]);
				$params[$key] = $value;
			}
		}
		list($class, $method) = self::$action_mapper[$action];
		$class = 'App\\Controller\\'.$class;
		$instance = new $class($params);
		return call_user_func_array([$instance, $method], $args);
	}

	public function exceptionHandler($e) {
		// echo get_class($e).': '.$e->getMessage().PHP_EOL;
		echo $e.PHP_EOL;
	}

	public static function terminate() {
		echo PHP_EOL.'Ctrl-C pressed, exiting now...'.PHP_EOL;
		exit();
	}

	private static function usage() {
		echo <<<EOD
Usage: php run.php [options...] sub-command [args...] [--] [params]

Options:
  -f, --fresh      Fresh start with new database
  -h, --help       This help

  args...          Arguments passed to script

  params...        Parameters passed to script. Use -- when first parameter

Examples:
  # Init all categories.
  php run.php initAllCategory

  # Init all products with given manufacturer.
  php run.php initProductByManu infineon-technologies-ag

  # Init all products with given manufacturer and product line.
  php run.php initProductByManuPL infineon-technologies-ag "IGBT Chip"

  # Fresh start with new database and only init 10 rows.
  php run.php -f initProductByManu infineon-technologies-ag -- limit=10

  # Dump json file for Tencent Cloud.
  php run.php dumpTxCloudJson

EOD;
		exit();
	}

}