<?php

namespace App\Model;

use App\Core\DB;
use App\Core\HttpClient;

class Manufacturer extends AbstractModel {

	const TABLE_NAME = 'manufacturers';
	const UNIQUE_KEY = 'name';

	const CLOUD_PATH = 'cloud://dev-03f389.6465-dev-03f389/';
	const LOGO_DIR = 'data/logo/';

	protected $_hidden_fields = ['url', 'logo_url', 'enabled'];

	protected static $first;
	protected static $upsert;
	protected static $update;

	// public function jsonSerialize() {
	// 	$data = parent::jsonSerialize();
	// 	$data['logo'] = self::CLOUD_PATH.$data['logo'];
	// 	return $data;
	// }

	public static function newInstance($data) {
		$name = $data->title;
		$url = '/zh-cn/manufacturers/'.str_replace(' ', '-', strtolower($data->name));
		return self::newInstance2($name, $url);
	}

	public static function newInstance2($name, $url) {
		$fields['name'] = $name;
		$fields['url'] = $url;
		$manufacturer = parent::firstOrCreate($fields);
		if(empty($manufacturer->description) || empty($manufacturer->logo_url) || empty($manufacturer->logo)) {
			$manufacturer->loadExtra();
		}
		return $manufacturer;
	}

	public function loadExtra() {
		static::$update = DB::getInstance()->prepare('UPDATE '.static::TABLE_NAME.' SET description=?, logo_url=?, logo=? WHERE id = ?;');
		$data = [
			'description' => '',
			'logo_url' => '',
			'logo' => '',
			'id' => $this->_data['id']
		];
		$response = HttpClient::getInstance()->request($this->_data['url']);
		if(preg_match('|<p class="ReadMore" data-more-text="读取更多" data-less-text="Read less">(.*)</p>|isU', $response, $match)) {
			$data['description'] = strip_tags(preg_replace('|(<br(\s*)/?>)+|i', "\n", $match[1]));
		}
		if(preg_match('|<img alt=".*" class="ManufacturerTabs-heroBanner-logo" src="(.*)" />|isU', $response, $match)) {
			$data['logo_url'] = $match[1];
			$data['logo'] = str_replace(['-logo.', '-logo-approved.'], ['', ''], basename(parse_url($data['logo_url'], PHP_URL_PATH)));
		}
		static::$update->execute(array_values($data));
		$this->_data['description'] = $data['description'];
		$this->_data['logo_url'] = $data['logo_url'];
		$this->_data['logo'] = $data['logo'];
	}

	public function save() {
		$sth = DB::getInstance()->prepare('UPDATE '.static::TABLE_NAME.' SET logo=? WHERE id = ?;');
		$sth->execute([$this->_data['logo'], $this->_data['id']]);
	}

	public function loadLogo() {
		$logofile = self::LOGO_DIR.basename($this->_data['logo']);
		if(file_exists($logofile) && self::checkLogo($logofile)) {
			return true;
		}
		try {
			HttpClient::getInstance()->request($this->_data['logo_url'], [
				// 'headers' => ['Referer' => 'https://www.arrow.com'.$this->_data['url']],
				// 'curl' => [\CURLOPT_REFERER => 'https://www.arrow.com'.$this->_data['url']],
				'sink' => $logofile
			]);
			$size = getimagesize($logofile);
			if(!$size) {
				fwrite(STDERR, basename($logofile).': Invalid Image'.PHP_EOL);
				@unlink($logofile);
				return false;
			}
			return true;
		}
		catch(\GuzzleHttp\Exception\RequestException $e) {
			fwrite(STDERR, basename($logofile).' download failed'.PHP_EOL.$e->getMessage().PHP_EOL);
			@unlink($logofile);
			return false;
		}
	}

}