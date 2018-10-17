<?php

namespace App\Model;

class Category extends AbstractModel {

	const TABLE_NAME = 'categories';
	const UNIQUE_KEY = 'name';

	protected static $first;
	protected static $upsert;

	protected $_hidden_fields = ['search_title', 'url', 'original_data', 'enabled'];

	public static function newInstance($data) {
		if(isset($data->children) && is_array($data->children) && count($data->children) > 0) {
			foreach($data->children as $child) {
				self::newInstance($child);
			}
		}
		$fields['category_id'] = $data->categoryId;
		$fields['name'] = $data->name;
		$fields['title'] = $data->title;
		$fields['search_title'] = $data->searchTitle;
		$fields['count'] = $data->count;
		$fields['level'] = $data->level;
		$fields['url'] = $data->url;
		$fields['original_data'] = json_encode($data);
		return parent::firstOrCreate($fields);
	}

	public function parent() {
		if($this->_data['level'] > 1) {
			return parent::getFirst(['category_id' => $this->_data['category_id'], 'level' => 1], true);
		}
	}

}