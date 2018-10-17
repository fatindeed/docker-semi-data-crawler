<?php

namespace App\Core;

class HttpClient {

	use Singleton;

	private $client;
	private $cookie_jar;
    
    /**
     * private construct, prevent new class directly
     */
	private function __construct() {
		$this->cookie_jar = new \GuzzleHttp\Cookie\FileCookieJar(sys_get_temp_dir().DIRECTORY_SEPARATOR.'guzzlehttp@www.arrow.com', true);
		$this->setcookie('website#lang', 'zh-CN');
		$this->client = new \GuzzleHttp\Client([
			'base_uri' => 'https://www.arrow.com',
			'connect_timeout' => 5,
			'timeout' => 30,
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36',
				'Accept-Encoding' => 'gzip, deflate, br',
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
				'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
			],
			'cookies' => $this->cookie_jar,
			// 'debug' => true
		]);
	}

	public function setcookie($name, $value) {
		$cookie = $this->cookie_jar->getCookieByName($name);
		if($cookie) {
			$cookie->setValue($value);
		}
		else {
			$cookie = new \GuzzleHttp\Cookie\SetCookie([
				'Name' => $name,
				'Value' => $value,
				'Domain' => '.arrow.com',
				'Path' => '/',
				// 'Max-Age' => 86400,
				// 'Expires' => 1,
			]);
		}
		return $this->cookie_jar->setCookie($cookie);
	}

	/**
	 * @see GuzzleHttp\Client::get
	 */
	public function request($uri = '', $options = []) {
		if(empty($options['sink'])) {
			$options['sink'] = sys_get_temp_dir().DIRECTORY_SEPARATOR.basename($uri);
		}
		$response = $this->client->get($uri, $options);
		return $response->getBody();
	}

}