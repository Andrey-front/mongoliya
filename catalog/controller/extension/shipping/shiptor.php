<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ControllerExtensionShippingShiptor extends Controller {
	public function __construct($registry) {
		parent::__construct($registry);
		
		$registry->set('shiptor', new Shiptor($registry));
	}
	
	// сохранение выбранного ПВЗ с карты
	public function address() {
		$this->language->load('extension/shipping/shiptor');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['kladr_id'], $this->request->post['courier_id'], $this->request->post['id'])) {
			unset($this->session->data['shiptor']['address']);
			$this->load->model('extension/shipping/shiptor');
			$kladr_id   = $this->request->post['kladr_id'];
			$courier_id = $this->request->post['courier_id'];
			$point_id   = $this->request->post['id'];
			
			$params = array(
				'kladr_id'		=> $kladr_id,
				'shipping_method'	=> $courier_id,
			);

			$products = $this->cart->getProducts();
		
			// Расчёт габаритов
			if($this->config->get('shipping_shiptor_size')){
				$params['width'] = $this->config->get('shipping_shiptor_default_width');
				$params['length'] = $this->config->get('shipping_shiptor_default_length');
				$params['height'] = $this->config->get('shipping_shiptor_default_height');
			}elseif (1 == sizeof($products)) {
				$product = reset($products);
				
				if (1 == $product['quantity']) {
					$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product));
					
					if($params['width'] > 0){
						$params['width'] = $params['width'];
					}else{
						$params['width'] = $this->config->get('shipping_shiptor_default_width');
					}
					
					if($params['length'] > 0){
						$params['length'] = $params['length'];
					}else{
						$params['length'] = $this->config->get('shipping_shiptor_default_length');
					}
					
					if($params['height'] > 0){
						$params['height'] = $params['height'];
					}else{
						$params['height'] = $this->config->get('shipping_shiptor_default_height');
					}
				}else{
					$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product));
					if($params['width'] > 0){
						$width = $params['width'];
					}else{
						$width = $this->config->get('shipping_shiptor_default_width');
					}
					
					if($params['length'] > 0){
						$length = $params['length'];
					}else{
						$length = $this->config->get('shipping_shiptor_default_length');
					}
					
					if($params['height'] > 0){
						$height = $params['height'];
					}else{
						$height = $this->config->get('shipping_shiptor_default_height');
					}
					
					$array_max_volume_product[] = $height;
					$array_max_volume_product[] = $length;
					$array_max_volume_product[] = $width;
					
					$max_volume_product = max($array_max_volume_product);
					$sumV = $height*$length*$width*$product['quantity'];
					$sumVQ = pow($sumV, 1/3);
					$sumVQ = ceil($sumVQ);
					
					if($sumVQ > $max_volume_product){
						$params['height'] = $sumVQ;
						$params['length'] = $sumVQ;
						$params['width'] = $sumVQ;
					}else{
						$params['length'] = $max_volume_product;
						$params['width'] = sqrt($sumV/$max_volume_product);
						$params['height'] = sqrt($sumV/$max_volume_product);
						
					}
				}
			}else{
				$sumV = 0;
				
				foreach($products as $product){
					$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product));
					
					if($params['width'] > 0){
						$width = $params['width'];
					}else{
						$width = $this->config->get('shipping_shiptor_default_width');
					}
					
					if($params['length'] > 0){
						$length = $params['length'];
					}else{
						$length = $this->config->get('shipping_shiptor_default_length');
					}
					
					if($params['height'] > 0){
						$height = $params['height'];
					}else{
						$height = $this->config->get('shipping_shiptor_default_height');
					}
					
					$array_max_volume_product[] = $height;
					$array_max_volume_product[] = $length;
					$array_max_volume_product[] = $width;
					
					$sumV += $height*$length*$width*$product['quantity'];
				}
				
				
				$max_volume_product = max($array_max_volume_product);
				
				$height = 0;
				$length = 0;
				$width = 0;
				
				$sumVQ = pow($sumV, 1/3);
				$sumVQ = ceil($sumVQ);
				
				if($sumVQ > $max_volume_product){
					$params['height'] = $sumVQ;
					$params['length'] = $sumVQ;
					$params['width'] = $sumVQ;
				}else{
					$params['length'] = $max_volume_product;
					$params['width'] = sqrt($sumV/$max_volume_product);
					$params['height'] = sqrt($sumV/$max_volume_product);
				}
			}
			
			$params['length'] = ceil($params['length']);
			$params['width'] = ceil($params['width']);
			$params['height'] = ceil($params['height']);

			$params['weight'] = $this->model_extension_shipping_shiptor->weight();

			$cache = 'shiptor.shipping.points.' . md5(implode('', $params));

			if (! $points = $this->cache->get($cache)) {
				$points = $this->shiptor->getDeliveryPoints($params);
				$this->cache->set($cache, $points);
			}

			if ($points) {
				foreach ($points as $point) {				
					if ($point['id'] == $point_id) {
						$this->session->data['shiptor']['address'] = array(
							'point_id' 	 => $point_id,
							'courier_id' => $courier_id,
							'point' 	 => $point['address'],
							'courier' 	 => $point['courier'],
						);
					
						unset($this->session->data['shiptor']['payment'][$courier_id]);

						if (! empty($point['cod']) && ! empty($point['card'])) {
							$this->session->data['shiptor']['payment'][$courier_id] = 'all_types';
						} else if (empty($point['cod']) && ! empty($point['card'])) {
							$this->session->data['shiptor']['payment'][$courier_id] = 'card';
						} else if (! empty($point['cod']) && empty($point['card'])) {
							$this->session->data['shiptor']['payment'][$courier_id] = 'cod';
						}

						break;
					}
				}
			}
		}
	}

	# Запрос POST получает список ПВЗ
	public function points() {
		$json = array();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->session->data['shiptor']['kladr_id'], $this->request->post['shipping_method'])) {
			$this->load->model('extension/shipping/shiptor');		
			
			$shipping = $this->model_extension_shipping_shiptor->getValidate($this->request->post['shipping_method']);

			if ($shipping && $shipping['shipping'] == 'P') {				
				$params = array(
					'kladr_id'		=> $this->session->data['shiptor']['kladr_id'],
					'shipping_method'	=> $shipping['courier_id'],
				);

				$products = $this->cart->getProducts();
		
				// Расчёт габаритов
				if($this->config->get('shipping_shiptor_size')){
					$params['width'] = $this->config->get('shipping_shiptor_default_width');
					$params['length'] = $this->config->get('shipping_shiptor_default_length');
					$params['height'] = $this->config->get('shipping_shiptor_default_height');
				}elseif (1 == sizeof($products)) {
					$product = reset($products);
					
					if (1 == $product['quantity']) {
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product));
						
						if($params['width'] > 0){
							$params['width'] = $params['width'];
						}else{
							$params['width'] = $this->config->get('shipping_shiptor_default_width');
						}
						
						if($params['length'] > 0){
							$params['length'] = $params['length'];
						}else{
							$params['length'] = $this->config->get('shipping_shiptor_default_length');
						}
						
						if($params['height'] > 0){
							$params['height'] = $params['height'];
						}else{
							$params['height'] = $this->config->get('shipping_shiptor_default_height');
						}
					}else{
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product));
						if($params['width'] > 0){
							$width = $params['width'];
						}else{
							$width = $this->config->get('shipping_shiptor_default_width');
						}
						
						if($params['length'] > 0){
							$length = $params['length'];
						}else{
							$length = $this->config->get('shipping_shiptor_default_length');
						}
						
						if($params['height'] > 0){
							$height = $params['height'];
						}else{
							$height = $this->config->get('shipping_shiptor_default_height');
						}
						
						$array_max_volume_product[] = $height;
						$array_max_volume_product[] = $length;
						$array_max_volume_product[] = $width;
						
						$max_volume_product = max($array_max_volume_product);
						$sumV = $height*$length*$width*$product['quantity'];
						$sumVQ = pow($sumV, 1/3);
						$sumVQ = ceil($sumVQ);
						
						if($sumVQ > $max_volume_product){
							$params['height'] = $sumVQ;
							$params['length'] = $sumVQ;
							$params['width'] = $sumVQ;
						}else{
							$params['length'] = $max_volume_product;
							$params['width'] = sqrt($sumV/$max_volume_product);
							$params['height'] = sqrt($sumV/$max_volume_product);
						}
					}
				}else{
					$sumV = 0;
					
					foreach($products as $product){
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product));
						
						if($params['width'] > 0){
							$width = $params['width'];
						}else{
							$width = $this->config->get('shipping_shiptor_default_width');
						}
						
						if($params['length'] > 0){
							$length = $params['length'];
						}else{
							$length = $this->config->get('shipping_shiptor_default_length');
						}
						
						if($params['height'] > 0){
							$height = $params['height'];
						}else{
							$height = $this->config->get('shipping_shiptor_default_height');
						}
						
						$array_max_volume_product[] = $height;
						$array_max_volume_product[] = $length;
						$array_max_volume_product[] = $width;
						
						$sumV += $height*$length*$width*$product['quantity'];
					}
					
					
					$max_volume_product = max($array_max_volume_product);
					
					$height = 0;
					$length = 0;
					$width = 0;
					
					$sumVQ = pow($sumV, 1/3);
					$sumVQ = ceil($sumVQ);
					
					if($sumVQ > $max_volume_product){
						$params['height'] = $sumVQ;
						$params['length'] = $sumVQ;
						$params['width'] = $sumVQ;
					}else{
						$params['length'] = $max_volume_product;
						$params['width'] = sqrt($sumV/$max_volume_product);
						$params['height'] = sqrt($sumV/$max_volume_product);
					}
				}
				
				$params['length'] = ceil($params['length']);
				$params['width'] = ceil($params['width']);
				$params['height'] = ceil($params['height']);
				
				$params['weight'] = $this->model_extension_shipping_shiptor->weight();
		
				$cache = 'shiptor.shipping.points.' . md5(implode('', $params));

				if (!$points = $this->cache->get($cache)) {
					$points = $this->shiptor->getDeliveryPoints($params);
					$this->cache->set($cache, $points);
				}

				if ($points) {
					$json['type'] = 'FeatureCollection';

					$position = end($points);
					
					$json['position'] = array();
					
					$json['position']['location'] = array($position['gps_location']['latitude'], $position['gps_location']['longitude']);

					$json['features'] = array();

					foreach ($points as $key => $point) {				
						if (! empty($point['limits']['max_weight']['value'])) {
							if ($params['weight'] > $point['limits']['max_weight']['value']) {
								continue;
							}
						}
			
						if (empty($point['card']) && empty($point['cod'])) {
							$no_pay = true;
						} else {
							$no_pay = false;
						}

						$json['features'][] = array(
							'type'       => 'Feature',
							'id'         => $point['id'],
							'geometry'   => array('type' => 'Point', 'coordinates' => array($point['gps_location']['latitude'], $point['gps_location']['longitude'])),
							'properties' => array(
							'address'     => $point['address'],		
							'id'          => $point['id'],
							'phones' 	  => ! empty($point['phones']) ? $point['phones'] : '',
							'description' => ! empty($point['trip_description']) ? $point['trip_description'] : '',
							'work' 		  => ! empty($point['work_schedule']) ? $point['work_schedule'] : '',
							'cod' 		  => ! empty($point['cod']) ? $point['cod'] : '',
							'card' 		  => ! empty($point['card']) ? $point['card'] : '',
							'courier' 	  => $point['courier'],
							'courier_id'  => $shipping['courier_id'],
							'kladr_id'    => $point['kladr_id'],
							'no_pay'	  => $no_pay
							)
						);
					}
				}
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}

	public function save() {
		unset($this->session->data['shiptor']['terms']);
		
		if ($this->config->get('shipping_shiptor_time') && ! empty($this->request->post['terms'])) {			
			$this->session->data['shiptor']['terms'] = $this->request->post['terms'];
		}
		
		if(isset($this->request->post['shipping_method'])){
			if(!empty($this->request->post['shipping_method'])){
				$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_couriers`');
				foreach($query->rows as $row){
					$this->session->data['shiptor']['payment'][$row['id']] = 'all_types'; 
				}
			}
		}
		
		if(isset($this->request->post['validate_kladr'])){
			$this->session->data['validate_kladr'] = 1;
		}else{
			$this->session->data['validate_kladr'] = 0;
		}
	
		if (! empty($this->request->post['kladr_id'])) {
			$this->session->data['shiptor']['kladr_id'] = $this->request->post['kladr_id'];
			unset($this->session->data['shiptor']['address']);
			unset($this->session->data['shipping_address']['address_1']);
		}else{
			unset($this->session->data['shiptor']['address']);
		}
	}
	
	public function getKladr(){
		
		$cleanAreas = array('город','республика','область','ао','край','автономный','округ','г.','обл.');
		$results = $params = array();
		$country_id = isset($this->request->post['country'])?$this->request->post['country']:($this->config->get('config_country_id')!=0?$this->config->get('config_country_id'):0);
		if($country_id!=0){
			$this->load->model('localisation/country');
			$this->load->model('localisation/zone');
			$country_info = $this->model_localisation_country->getCountry($country_id);
			$zones = array();
			foreach($this->model_localisation_zone->getZonesByCountryId($country_id) as $zone){
				$zone['name'] = mb_strtolower($zone['name']);
				$zone['name'] = str_replace($cleanAreas,'',$zone['name']);
				$zones[$zone['zone_id']] = trim($zone['name']);
			}
			$params['country_code'] = $country_info['iso_code_2'];
		}
		$json = array();
		$json['searchContext'] = $this->request->post;
		
		$params['query'] = isset($this->request->post['query'])?trim($this->request->post['query']):'';
		$suggests = $this->shiptor->suggestSettlement($params);
		
		if($suggests){
			foreach($suggests as $i=>$result){
				$results[$i] = $result;
				if(!empty($zones)){
					if($result['readable_parents']!=''){
						$area = mb_strtolower($result['readable_parents']);
						$area = trim(str_replace($cleanAreas,'',$area));
						$area = explode(',',$area);
						$results[$i]['zone_id'] = array_search(trim($area[0]),$zones);
						if(!$results[$i]['zone_id'] && isset($area[1])) $results[$i]['zone_id'] = array_search(trim($area[1]),$zones);
					}
					else {
						$results[$i]['zone_id'] = array_search(trim(mb_strtolower($result['name'])),$zones);
					}
				}
			}
		}
		
		$json['result'] = array_slice($results, 0, isset($this->request->post['limit'])?(int)$this->request->post['limit']:30);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}