<?php

namespace App\Core;

class SignalHandler {

	private static $pcntl_loaded;

	/**
	 * Installs a signal handler
	 */
	public static function init($handler) {
		self::$pcntl_loaded = extension_loaded('pcntl');
		if(self::$pcntl_loaded) {
			pcntl_signal(\SIGINT, $handler);
		}
		else {
			trigger_error('pcntl extension is not loaded.', E_USER_NOTICE);
		}
	}

	/**
	 * Calls signal handlers for pending signals
	 */
	public static function dispatch() {
		if(self::$pcntl_loaded) {
			pcntl_signal_dispatch();
		}
	}

}