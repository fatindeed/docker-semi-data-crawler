<?php

namespace App\Controller;

use App\Core\HttpClient;

class Category extends AbstractController {

	public function getAll() {
		$response = HttpClient::getInstance()->request('/zh-cn/product-category-sitemap');
		if(!preg_match_all('|<h2 class="Sitemap-heading">\s*<a href="(\S+)" class="u-textArrow">(.*)</h2>|isU', $response, $matches, PREG_SET_ORDER)) {
			throw new \RuntimeException('no header found', 1);
		}
		foreach($matches as $match) {
			$response = HttpClient::getInstance()->request($match[1]);
			if(!preg_match('|<a href="/zh-cn/products/search\?cat=(\S+)" class="Button Button--red">查看全部</a>|isU', $response, $match)) {
				throw new \RuntimeException('no cat found', 1);
			}
			$cat_name = urldecode($match[1]);
			echo 'Processing category - '.$cat_name.' ...';
			$response = HttpClient::getInstance()->request('/productsearch/searchresultsajax', ['query' => [
				'page' => 1,
				'q' => '',
				'cat' => $cat_name,
				'promoGroupLevel' => 'main',
				'perPage' => 10
			]]);
			$res = json_decode($response);
			if(json_last_error() != JSON_ERROR_NONE) {
				throw new \RuntimeException(json_last_error_msg(), json_last_error());
			}
			if($res->error) {
				throw new \RuntimeException('result error: '.$res->error, 1);
			}
			foreach($res->data->facetContainer->categoryFacets as $categoryFacet) {
				\App\Model\Category::newInstance($categoryFacet);
			}
			echo ' done.'.PHP_EOL;
		}
	}

}