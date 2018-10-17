<?php

namespace App\Controller;

use App\Core\HttpClient;

class Product extends AbstractController {

	private $perPage = 100;
	private $limit = 0;

	public function __construct($otpions = []) {
		parent::__construct($otpions);
		if($otpions['limit'] > 0) {
			$this->limit = $otpions['limit'];
		}
		if($this->limit > 0 && $this->limit < $this->perPage) {
			$this->perPage = $this->limit;
		}
		HttpClient::getInstance()->setcookie('arrowcurrency', 'isocode=CNY&culture=zh-CN');
	}

	/**
	 * @example https://www.arrow.com/productsearch/manufacturerseosearchajax/infineon-technologies-ag/view-all?page=1&q=&promoGroupLevel=main&perPage=10
	 */
	public function getByManufacturer($manufacturer) {
		$this->run('/productsearch/manufacturerseosearchajax/'.$manufacturer.'/view-all', [
			'page' => 1,
			'q' => '',
			'promoGroupLevel' => 'main'
		]);
	}

	/**
	 * @example https://www.arrow.com/productsearch/manufacturerseosearchajax/infineon-technologies-ag/cat_diodes%2c%20transistors%20and%20thyristors?page=1&q=&promoGroupLevel=main&perPage=10
	 */
	public function getByManufacturerCategory($manufacturer, $category) {
		$this->run('/productsearch/manufacturerseosearchajax/'.$manufacturer.'/cat_'.strtolower($category), [
			'page' => 1,
			'q' => '',
			'promoGroupLevel' => 'main'
		]);
	}

	/**
	 * @example https://www.arrow.com/productsearch/manufacturerseosearchajax/infineon-technologies-ag/prodline_igbt%20chip?page=1&q=&promoGroupLevel=pl&perPage=10
	 */
	public function getByManufacturerProductLine($manufacturer, $prodline) {
		$this->run('/productsearch/manufacturerseosearchajax/'.$manufacturer.'/prodline_'.rawurlencode(strtolower($prodline)), [
			'page' => 1,
			'q' => '',
			'promoGroupLevel' => 'pl'
		]);
	}

	/**
	 * @example https://www.arrow.com/productsearch/productlinesearchresultajax?page=1&q=&prodline=IGBT+Chip&promoGroupLevel=pl&perPage=10
	 */
	public function getByProductLine($prodline) {
		$this->run('/productsearch/productlinesearchresultajax', [
			'page' => 1,
			'q' => '',
			'prodline' => $prodline,
			'promoGroupLevel' => 'pl'
		]);
	}

	/**
	 * @example https://www.arrow.com/productsearch/searchresultsajax?page=1&q=&subcat=Diodes%2C+Transistors+and+Thyristors-sep-IGBT+Transistors&promoGroupLevel=sub&perPage=10
	 * @example https://www.arrow.com/productsearch/searchresultsajax?page=1&q=&prodLine=IGBT+Chip&promoGroupLevel=pl&perPage=10
	 */
	public function get($params) {
		$params['page'] = 1;
		$params['q'] = '';
		$this->run('/productsearch/searchresultsajax', $params);
	}

	private function run($url, $params) {
		do {
			if($this->perPage > 0) {
				$params['perPage'] = $this->perPage;
			}
			echo 'Processing data '.($params['perPage'] * ($params['page'] - 1) + 1).' - '.($params['perPage'] * $params['page']);
			$response = HttpClient::getInstance()->request($url, ['query' => $params]);
			$res = json_decode($response);
			if(json_last_error() != JSON_ERROR_NONE) {
				throw new \RuntimeException(json_last_error_msg(), json_last_error());
			}
			if($res->error) {
				throw new \RuntimeException('result error: '.$res->error, 1);
			}
			foreach($res->data->facetContainer->facets as $facet) {
				if($facet->urlName == 'Manufacturer_name') {
					foreach($facet->facetValues as $facetValue) {
						\App\Model\Manufacturer::newInstance($facetValue);
					}
				}
			}
			foreach($res->data->facetContainer->categoryFacets as $categoryFacet) {
				\App\Model\Category::newInstance($categoryFacet);
			}
			foreach($res->data->results as $result) {
				\App\Model\Part::newInstance($result);
			}
			echo ' / '.$res->data->resultsMetadata->totalResultCount.' done.'.PHP_EOL;
			if($this->limit > 0) {
				$res->data->resultsMetadata->totalPageCount = ceil($this->limit / $this->perPage);
			}
		}
		while($params['page']++ < $res->data->resultsMetadata->totalPageCount);
	}

}