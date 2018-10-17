<?php

namespace App\Model;

class BuyingOption extends AbstractModel {

	const TABLE_NAME = 'buying_options';
	const UNIQUE_KEY = 'source_part_id';

	protected static $first;
	protected static $upsert;

	protected $_hidden_fields = ['packaging_type', 'date_code', 'unit_of_measure', 'current_iso_currency', 'original_data'];

	public function jsonSerialize() {
		$data = parent::jsonSerialize();
		$data['pipelines'] = json_decode($data['pipelines']);
		$data['price_bands'] = json_decode($data['price_bands']);
		return $data;
	}

	public static function newInstance($data) {
		// source_code: ACNA / EUROPE / NACR / C1S
		$fields['source_code'] = $data->sourceCode;
		$fields['source_part_id'] = $data->sourcePartId;
		$fields['packaging_type'] = $data->packagingType;
		$fields['quantity'] = $data->quantity;
		$fields['manufacturer_lead_time'] = $data->manufacturerLeadTime;
		$fields['date_code'] = $data->dateCode;
		$fields['order_increment'] = $data->orderIncrement;
		$fields['order_minimum_quantity'] = $data->orderMinimumQuantity;
		$fields['pipeline_total'] = $data->pipelineTotal;
		$fields['ships_from_country_name'] = $data->shipsFromCountryName;
		$fields['days_until_dispatch'] = $data->daysUntilDispatch;
		$fields['pipelines'] = json_encode($data->pipelines);
		$fields['price_bands'] = json_encode($data->priceBands);
		$fields['part_id'] = $data->partId;
		// inventory_region: NAC / EUROPE
		$fields['inventory_region'] = $data->inventoryRegion;
		$fields['unit_of_measure'] = $data->unitOfMeasure;
		$fields['origin_country_name'] = $data->originCountryName;
		$fields['current_iso_currency'] = $data->currentISOCurrency;
		$fields['lowest_price'] = $data->lowestPrice;
		$fields['highest_price'] = $data->highestPrice;
		$fields['original_data'] = json_encode($data);
		return parent::firstOrCreate($fields);
	}

}