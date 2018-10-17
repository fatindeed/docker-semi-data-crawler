<?php

namespace App\Core;

class DB {

	use Singleton;

	private $dbh;
    
    /**
     * private construct, prevent new class directly
     */
	private function __construct() {
	}

	public function connect($data_file = null) {
		$this->dbh = new \PDO('sqlite:'.($data_file ? : ':memory:'));
		$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return $this;
	}

	public function __call($name, $arguments) {
		if(!method_exists($this->dbh, $name)) {
			trigger_error('PDO::'.$name.' method not exists.', E_USER_ERROR);
		}
		return call_user_func_array([$this->dbh, $name], $arguments);
	}

}