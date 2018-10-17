<?php

namespace App\Model;

use App\Core\DB;

abstract class AbstractModel implements \JsonSerializable {

	protected $_data = [];
	protected $_hidden_fields = ['original_data'];

	public function __construct() {
		\App\Core\SignalHandler::dispatch();
	}

	public function __set($name, $value) {
		$this->_data[$name] = $value;
	}

	public function __get($name) {
		if(isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		// $trace = debug_backtrace();
		// trigger_error(
		// 	'Undefined property via __get(): ' . $name .
		// 	' in ' . $trace[0]['file'] .
		// 	' on line ' . $trace[0]['line'],
		// 	E_USER_NOTICE);
		return null;
	}

	public function __isset($name) {
		return isset($this->_data[$name]);
	}

	public function __unset($name) {
		unset($this->_data[$name]);
	}

	public function jsonSerialize() {
		$data = ['_id' => $this->_data['id']] + $this->_data;
		unset($data['id']);
		foreach($this->_hidden_fields as $key) {
			unset($data[$key]);
		}
		return $data;
	}

	abstract public static function newInstance($data);

	public static function firstOrCreate($fields) {
		$first = self::getFirst([static::UNIQUE_KEY => $fields[static::UNIQUE_KEY]]);
		if($first) {
			return $first;
		}
		try {
			// self::$upsert = DB::getInstance()->prepare('INSERT INTO manufacturers (uname, name, logo_url, count) VALUES (?, ?, ?, ?) ON CONFLICT(uname) DO UPDATE SET name=excluded.name, logo_url=excluded.logo_url, count=excluded.count');
			static::$upsert = DB::getInstance()->prepare('INSERT INTO '.static::TABLE_NAME.' ('.implode(', ', array_keys($fields)).') VALUES ('.implode(', ', array_pad([], count($fields), '?')).');');
			static::$upsert->execute(array_values($fields));
			return self::firstOrCreate($fields);
		}
		catch(\PDOException $e) {
			// UNIQUE constraint failed
			if($e->getCode() == 23000 && $e->errorInfo[1] == 19) {
				fwrite(STDERR, $e->errorInfo[2].PHP_EOL);
			}
			else {
				throw $e;
			}
		}
	}

	private static function getStatement($where = []) {
		$clause = [];
		if(is_array($where) && count($where) > 0) {
			foreach ($where as $field => $value) {
				$clause[] = $field.' = ?';
			}
		}
		return 'SELECT * FROM '.static::TABLE_NAME.(count($clause) > 0 ? ' WHERE '.implode(' AND ', $clause) : '').';';
	}

	public static function getFirst($where = [], $throwExceptionWhileNoResult = false) {
		$sth = DB::getInstance()->prepare(self::getStatement($where));
		$sth->setFetchMode(\PDO::FETCH_CLASS, get_called_class());
		$sth->execute(array_values($where));
		$result = $sth->fetch(\PDO::FETCH_CLASS);
		if($result === false && $throwExceptionWhileNoResult) {
			throw new \RuntimeException('no result for '.static::TABLE_NAME.':'.json_encode($where), 1);
		}
		return $result;
	}

	public static function get($where = []) {
		$sth = DB::getInstance()->prepare(self::getStatement($where));
		$sth->execute(array_values($where));
		return $sth->fetchAll(\PDO::FETCH_CLASS, get_called_class());
	}

}