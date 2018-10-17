<?php

namespace App\Controller;

class Manufacturer extends AbstractController {

	public function getAll() {
		parent::mkdir(\App\Model\Manufacturer::LOGO_DIR);
		$response = HttpClient::getInstance()->request('/zh-cn/manufacturers');
		if(!preg_match_all('|<a class="ManufacturersTabs-results-item u-cf" href="/zh-cn/manufacturers/(.+)">.*<span class="ManufacturersTabs-results-item-text">(.*)</span>\s*<span class="ManufacturersTabs-results-item-num">(.*)</span>\s*</a>|isU', $response, $matches, PREG_SET_ORDER)) {
			throw new \RuntimeException('no data found', 1);
		}
		foreach($matches as $match) {
			\App\Model\Manufacturer::newInstance2(html_entity_decode($match[2]), $match[1]);
		}
	}

}