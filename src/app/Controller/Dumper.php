<?php

namespace App\Controller;

use App\Core\DB;
use Intervention\Image\ImageManagerStatic as Image;

class Dumper extends AbstractController {

	public function __construct($otpions = []) {
		parent::__construct($otpions);
		// init database
		$dbh = DB::getInstance();
		$this->countByManufacturer = $dbh->prepare('SELECT count(*) FROM parts WHERE manufacturer_id = ?;');
		$this->countByCategory = $dbh->prepare('SELECT count(*) FROM parts WHERE category_id = ?;');
		$this->countByParentCategory = $dbh->prepare('SELECT count(*) FROM parts WHERE parent_category_id = ?;');
	}

	public function txCloudJson() {
		$this->exportJSON('Manufacturer');
		$this->exportJSON('Category');
		$this->exportJSON('Part');
		$this->exportJSON('BuyingOption');
	}

	private function exportJSON($model, $where = []) {
		$content = '';
		$model_class = 'App\\Model\\'.$model;
		$results = call_user_func_array([$model_class, 'get'], [$where]);
		foreach($results as $result) {
			\App\Core\SignalHandler::dispatch();
			if(isset($result->count)) {
				switch($model) {
					case 'Manufacturer':
						$this->countByManufacturer->execute([$result->id]);
						$result->count = $this->countByManufacturer->fetchColumn();
						break;
					case 'Category':
						if($result->level > 1) {
							$this->countByCategory->execute([$result->id]);
							$result->count = $this->countByCategory->fetchColumn();
						}
						else {
							$this->countByParentCategory->execute([$result->id]);
							$result->count = $this->countByParentCategory->fetchColumn();
						}
						break;
					default:
						fwrite(STDERR, 'Can not count for '.$model.PHP_EOL);
				}
			}
			$content .= json_encode($result, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT).PHP_EOL;
		}
		$dumpfile = 'data/'.$model_class::TABLE_NAME.'.json';
		file_put_contents($dumpfile, $content);
		echo $model.' dumped to '.$dumpfile.PHP_EOL;
		unset($content);
	}

	public function logo() {
		$logo_width = 200;
		$logo_dir = 'data/logo/';
		// enter work dir
		parent::mkdir($logo_dir);
		chdir($logo_dir);
		// get data
		$manufacturers = call_user_func(['App\\Model\\Manufacturer', 'get']);
		foreach($manufacturers as $manufacturer) {
			\App\Core\SignalHandler::dispatch();
			$pos = strrpos($manufacturer->logo_url, '?');
			$logo_url = ($pos ? substr($manufacturer->logo_url, 0, $pos) : $manufacturer->logo_url);
			$logofile = str_replace(['-logo.', '-logo-approved.'], ['.', '.'], basename(parse_url($logo_url, PHP_URL_PATH)));
			if(!file_exists($logofile)) {
				exec('curl  -fsSL -o '.$logofile.' "'.$logo_url.'"');
			}
			$img = Image::make($logofile)->resize($logo_width, null, function($constraint) {
				$constraint->aspectRatio();
			});
			$newlogo = substr($logofile, 0, strrpos($logofile, '.')).'-logo'.image_type_to_extension(IMAGETYPE_PNG);
			$img->save($newlogo);
			$img->destroy();
			echo $newlogo.' saved.'.PHP_EOL;
		}
	}

	public function featureText() {
		$features = [];
		$results = call_user_func(['App\\Model\\Part', 'get']);
		foreach($results as $result) {
			$feature_data = json_decode($result->feature_data, true);
			if(empty($features)) {
				$features = array_keys($feature_data);
			}
			else {
				foreach($feature_data as $feature => $value) {
					if(array_search($feature, $features) === false) {
						var_dump($feature);
						print_r($features);
						print_r($feature_data);
						exit();
					}
				}
			}
		}
	}

}