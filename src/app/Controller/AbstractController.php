<?php

namespace App\Controller;

use App\Core\DB;

class AbstractController {

	protected $client;
	protected $cookie_jar;

	public function __construct($otpions = []) {
		// init database
		self::mkdir('data');
		if($otpions['fresh_db']) {
			@unlink('data/demo.db');
		}
		DB::getInstance()->connect('data/demo.db')->exec(file_get_contents('init.sql'));
	}

	public static function mkdir($dir) {
		if(!file_exists($dir)) {
			return mkdir($dir, 0777, true);
		}
		else {
			return true;
		}
	}

}