<?php

namespace App\Model;

class Part extends AbstractModel {

	const TABLE_NAME = 'parts';
	const UNIQUE_KEY = 'part_number';

	protected $_hidden_fields = ['download_url', 'npi', 'promo_group', 'promo_group_key', 'attributes', 'part_url', 'original_data'];

	protected static $first;
	protected static $upsert;

	public function jsonSerialize() {
		$data = parent::jsonSerialize();
		$data['feature_data'] = json_decode($data['feature_data']);
		$manufacturer = Manufacturer::getFirst(['id' => $data['manufacturer_id']], true);
		$data['manufacturer_name'] = $manufacturer->name;
		$category = Category::getFirst(['id' => $data['category_id']], true);
		$data['category_name'] = $category->title;
		return $data;
	}

	public static function newInstance($data) {
		$category = Category::getFirst(['title' => $data->category], true);
		$manufacturer = Manufacturer::newInstance2($data->manufacturer, $data->manufacturerUrl);
		if($data->groupedBuyingOptions->hasPricingData) {
			foreach($data->groupedBuyingOptions->regionalBuyingOptions as $regionalBuyingOption) {
				foreach($regionalBuyingOption->buyingOptions as $buyingOptionsData) {
					BuyingOption::newInstance($buyingOptionsData);
				}
			}
			unset($regionalBuyingOption, $buyingOptionsData);
		}
		$fields['id'] = $data->partId;
		$fields['part_number'] = $data->partNumber;
		$fields['download_url'] = $data->downloadUrl;
		$fields['image_url'] = $data->imageUrl;
		$fields['datasheet_url'] = $data->datasheetUrl;
		// new product i?
		$fields['npi'] = ($data->npi ? 1 : 0);
		$fields['promo_group'] = $data->promoGroup;
		$fields['promo_group_key'] = $data->promoGroupKey;
		$fields['category_id'] = $category->id;
		$fields['parent_category_id'] = $category->parent()->id;
		$fields['description'] = $data->description;
		$fields['short_description'] = $data->shortDescription;
		$fields['is_rohs_compliant'] = ($data->isRohsCompliant ? 1 : 0);
		$fields['attributes'] = json_encode($data->attributes);
		$fields['part_url'] = $data->partUrl;
		$fields['manufacturer_id'] = $manufacturer->id;
		$fields['feature_data'] = json_encode($data->featureData);
		$fields['eccn_code'] = $data->eccnCode;
		$fields['quantity'] = $data->quantity;
		$fields['original_data'] = json_encode($data);
		return parent::firstOrCreate($fields);
	}

}