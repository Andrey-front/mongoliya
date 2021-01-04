<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ModelExtensionShippingShiptor extends Model {
	private $error;
	private $cleanAreas = array('город','республика','область','ао','край','автономный','округ','г.','обл.');

	public function __construct($registry) {
		parent::__construct($registry);
		$registry->set('shiptor', new Shiptor($registry));

	}

	public function getQuote($address) {	
		$this->language->load('extension/shipping/shiptor');

		if (! $this->config->get('shipping_shiptor_status') && ! $this->config->get('shipping_shiptor_authorization')) {
			return;
		}

		$params = array();

		# Код города отправителя
		if ($this->config->get('shipping_shiptor_kladr_id')) {
			$params['kladr_id_from'] = $this->config->get('shipping_shiptor_kladr_id');
		}

		$address_id = 0;
		
		if ($this->customer->isLogged() && isset($this->session->data['shipping_address']['address_id'])){
			$kladr_id_array = $this->getKladr();
			$kladr_id = $this->getAddressId($this->session->data['shipping_address']['address_id']);
				
			foreach($kladr_id_array as $validate){
			$valid_kladr = array_search($kladr_id, $validate);
				if(!empty($valid_kladr)){
					$valid_kladr_id = $kladr_id;
				}elseif($validate['name'] == $this->session->data['shipping_address']['city'] && $validate['administrative_area'] == $this->session->data['shipping_address']['zone']){
					$region_array[] = $validate;
				}
			}
				 
			if(!empty($valid_kladr_id)){
				$this->session->data['shiptor']['kladr_id'] = $valid_kladr_id;
			}elseif(isset($region_array)){
				$kladr_id = $this->updateAddressId($this->session->data['shipping_address']['address_id'], $region_array[0]['kladr_id']);
				$this->session->data['shiptor']['kladr_id'] =  $region_array[0]['kladr_id'];
			}else{
				$kladr_id = $this->updateAddressId($this->session->data['shipping_address']['address_id'],$kladr_id_array[0]['kladr_id']);
				$this->session->data['shiptor']['kladr_id'] = $kladr_id_array[0]['kladr_id'];
			}
				
		}else{
			if (!isset($this->session->data['shiptor']['kladr_id'])) {
				if (!empty($address['city'])) {
					$result = $this->getSuggest($address);
					if (isset($result['kladr_id'])) {
						$this->session->data['shiptor']['kladr_id'] = $result['kladr_id'];
					}
				}
			}
		}
		if(isset($this->session->data['shiptor']['kladr_id']))$address_id = $this->session->data['shiptor']['kladr_id'];
		$params['kladr_id']  = $address_id;
		$params['country_code'] = isset($address['iso_code_2'])?$address['iso_code_2']:'RU';
		if(strlen($params['kladr_id'])<=1)return;

		$products = $this->cart->getProducts();
		
		// Расчёт габаритов
		if($this->config->get('shipping_shiptor_size')){
			$params['width'] = $this->config->get('shipping_shiptor_default_width');
			$params['length'] = $this->config->get('shipping_shiptor_default_length');
			$params['height'] = $this->config->get('shipping_shiptor_default_height');
		}elseif (1 == sizeof($products)) {
			$product = reset($products);
			
			if (1 == $product['quantity']) {
				$params = array_merge($params,$this->length($product));
				
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
				$params = array_merge($params,$this->length($product));
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
				$params = array_merge($params,$this->length($product));
				
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

		# Вес, кг
		$weight = $this->weight();

		$params['weight'] = $weight;

		$shiptor_total_status = $this->config->get('total_shiptor_total_status');
		
		$total = $this->cart->getTotal();
		
		if ($total) {
			$params['declared_cost'] = $total;
			
			// проверяем модуль "Shiptor - Учет суммы наложенного платежа"
			
			if (!empty($shiptor_total_status)) {
				
				$this->load->model('setting/extension');
				$results = $this->model_setting_extension->getExtensions('payment');
				
				foreach ($results as $result) {
					if ($this->config->get('payment_' . $result['code'] . '_status')) {
						if(false !== strpos($result['code'], 'shiptor_')){
							if($params['country_code'] == 'RU'){
								$this->load->model('extension/payment/' . $result['code']);

								$pay_sort[] = array(
									'sort_order'	=> $this->config->get('payment_' . $result['code'] . '_sort_order'),
									'code'			=> $result['code'],
								);
							}else{
								unset($this->session->data['payment_method']['code']);
							}
						}else{
							$this->load->model('extension/payment/' . $result['code']);
							
							$pay_sort[] = array(
								'sort_order'	=> $this->config->get('payment_' . $result['code'] . '_sort_order'),
								'code'			=> $result['code'],
							);
						}
					}
				}
				
				if(isset($pay_sort)){
					$pay = min($pay_sort);
						
					if (!isset($this->session->data['payment_method']['code'])){
						$this->session->data['payment_method']['code'] = $pay['code'];
					}
				}
				
				// "учет" включен
				// первый заход - считаем с наложенным платежом (еще нет данных по способу оплаты)
				// второй заход - считаем в зависимости от способа оплаты
				
				if (!isset($this->session->data['payment_method']['code']) || false !== strpos($this->session->data['payment_method']['code'], 'shiptor_')) {
					$params['cod'] = $total;
				} else {
					$params['cod'] = 0;
				}
			} else {
				
				$this->load->model('setting/extension');
				$results = $this->model_setting_extension->getExtensions('payment');

				foreach ($results as $result) {
					if ($this->config->get('payment_' . $result['code'] . '_status')) {
						if(false !== strpos($result['code'], 'shiptor_')){
							if($params['country_code'] == 'RU'){
								$this->load->model('extension/payment/' . $result['code']);

								$pay_sort[] = array(
									'sort_order'	=> $this->config->get('payment_' . $result['code'] . '_sort_order'),
									'code'			=> $result['code'],
								);
							}else{
								unset($this->session->data['payment_method']['code']);
							}
						}else{
							$this->load->model('extension/payment/' . $result['code']);
							
							$pay_sort[] = array(
								'sort_order'	=> $this->config->get('payment_' . $result['code'] . '_sort_order'),
								'code'			=> $result['code'],
							);
						}
					}
				}
				
				if(isset($pay_sort)){
					$pay = min($pay_sort);
						
					if (!isset($this->session->data['payment_method']['code'])){
						$this->session->data['payment_method']['code'] = $pay['code'];
					}
				}
				
				// "учет" выключен
				// перерасчета при смене способа оплаты не происходит, всегда считаем с наложкой
				
				if(false !== strpos($pay['code'], 'shiptor_')){
					$params['cod'] = $total;
				}else{
					$params['cod'] = 0;
				}
			}
		}

		$results1 = $this->shiptor->calculateShipping($params);
		$results2 = $this->shiptor->calculateShippingСutting($params);
		
		$results = array_merge_recursive ($results1, $results2);
		$quote_data = array();
		
		if (! empty($results['methods'])) {			
			# Сортируем способы доставки
			$sort_order = array();

			foreach ($results['methods'] as $key => $value) {
				$sort_order[$key] = $this->config->get('shipping_shiptor_' . $value['method']['id'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results['methods']);
			
			foreach ($results['methods'] as $value) {
				if ($value['status'] != 'unavailable') {

					$courier_id = $value['method']['id'];

					# получаем доступные виды доставок
					if ($shipping_method = $this->getShippingMethod($courier_id)) {
						$prefix = 'shipping_shiptor_' . $shipping_method['id'];
						if ($this->config->get($prefix . '_status')) {
							
							$price = $surcharge = 0;
							
							if (! empty($value['cost']['total'])) {
								$price = $value['cost']['total']['sum'];
							}
							
							$regions = $this->config->get('shipping_shiptor_regions');
							
							$id = substr($address_id, 0, -9);
							if(is_array($regions) && in_array($id,$regions) && $this->config->get( $prefix . '_' . $id . '_status')){//если найден регион - показываем по нему
								if ($region_info = $this->getRegionId($id)) {
									$region_prefix = 'shipping_shiptor_' . $shipping_method['id'] . '_' . $region_info['id'];
									
									if ($this->config->get($region_prefix . '_hide')) {
										continue;
									}
									
									# Минимальная цена
									if ($this->config->get($region_prefix . '_total_min')) {
										if ($this->cart->getSubTotal() < $this->config->get($region_prefix . '_total_min')) {
											continue;
										}
									}
									
									#Максимальная цена
									if ($this->config->get($region_prefix . '_total_max')) {
										if ($this->cart->getSubTotal() > $this->config->get($region_prefix . '_total_max')) {
											continue;
										}
									}												
									
									# Минимальная вес
									if ($this->config->get($region_prefix . '_total_min_weight')) {
										if ($weight < $this->config->get($region_prefix . '_total_min_weight')) {
											continue;
										}
									}
									
									#Максимальная вес
									if ($this->config->get($region_prefix . '_total_max_weight')) {
										if ($weight > $this->config->get($region_prefix . '_total_max_weight')) {
											continue;
										}
									}
									
									# Фиксированная цена
									if ($this->config->get($region_prefix . '_fixed')) {
										$price = $this->config->get($region_prefix . '_fixed');
									}
									
									# Доставка в зависимости от веса	
									if ($this->config->get($region_prefix . '_weight_price')) {
										$rates = explode(',', $this->config->get($region_prefix . '_weight_price'));
										$rate_price = array();
										foreach ($rates as $rate) {
											$data = explode(':', $rate);
											$rate_price[(int)$data[0]] = (float)$data[1];
										}
										krsort($rate_price);
										foreach ($rate_price as $p=>$rate) {
											if ($p<=$weight) {
												if (isset($rate)) {
													$price = $rate;
												}
												break;
											}
										}
									}
									
									# Наценка фиксированной суммой
									if ($this->config->get($region_prefix . '_type') == 'F') {
										if ($price && $this->config->get($region_prefix . '_markup')) {
											$price = ($price + $this->config->get($region_prefix . '_markup'));
										}
									}
									
									# Наценка в %
									if ($this->config->get($region_prefix . '_type') == 'P') {
										if ($price && $this->config->get($region_prefix . '_markup')) {
											$price = $price * (($this->config->get($region_prefix . '_markup') / 100) + 1);
										}
									}
									
									# Бесплатная доставка
									if ($this->config->get($region_prefix . '_total_free')) {
										if ($this->cart->getSubTotal() >= $this->config->get($region_prefix . '_total_free')) {
											$price = 0;
										}
									}
								}
							}
							else{ //иначе показываем смотря в общие настройки
								# Минимальная цена
								if ($this->config->get($prefix . '_total_min')) {
									if ($this->cart->getSubTotal() < $this->config->get($prefix . '_total_min')) {
										continue;
									}
								}
								
								#Максимальная цена
								if ($this->config->get($prefix . '_total_max')) {
									if ($this->cart->getSubTotal() > $this->config->get($prefix . '_total_max')) {
										continue;
									}
								}
								
								# Минимальная вес
								if ($this->config->get($prefix . '_total_min_weight')) {
									if ($weight < $this->config->get($prefix . '_total_min_weight')) {
										continue;
									}
								}
								
								#Максимальная вес
								if ($this->config->get($prefix . '_total_max_weight')) {
									if ($weight > $this->config->get($prefix . '_total_max_weight')) {
										continue;
									}
								}
								
								# Фиксированная стоимость
								if ($this->config->get($prefix . '_fixed')) {
									$price = $this->config->get($prefix . '_fixed');
								}
								
								# Доставка в зависимости от веса
								if ($this->config->get($prefix . '_weight_price')) {
									$rates = explode(',', $this->config->get($prefix . '_weight_price'));
									$rate_price = array();
									foreach ($rates as $rate) {
										$data = explode(':', $rate);
										$rate_price[(int)$data[0]] = (float)$data[1];
									}
									krsort($rate_price);
									foreach ($rate_price as $p=>$rate) {
										if ($p<=$weight) {
											if (isset($rate)) {
												$price = $rate;
											}
											break;
										}
									}
								}
								
								# Наценка фиксированной суммой 
								if ($this->config->get($prefix . '_type') == 'F') {
									if ($price && $this->config->get($prefix . '_markup')) {
										$price = ($price + $this->config->get($prefix . '_markup'));
									}
									
									if ($this->config->get($prefix . '_markup_surcharge')) {
										$surcharge = $surcharge + $this->config->get($prefix . '_markup_surcharge');
									}
								}
								
								# Наценка в %
								if ($this->config->get($prefix . '_type') == 'P') {
									if ($price && $this->config->get($prefix . '_markup')) {
										$price = ($price * (($this->config->get($prefix . '_markup') / 100) + 1));
									}
									
									if ($this->config->get($prefix . '_markup_surcharge')) {
										$surcharge = ($surcharge * (($this->config->get($prefix . '_markup_surcharge') / 100) + 1));
									}
								}
								
								# Бесплатная доставка
								if ($this->config->get($prefix . '_total_free')) {
									if ($this->cart->getSubTotal() >= $this->config->get($prefix . '_total_free')) {
										$price = 0;
									}
								}
							}
							
							# Доставка Shiptor 1 Days
							if ($this->config->get($prefix . '_time')!='' && ($shipping_method['courier'] == 'shiptor-one-day') && (time() >= strtotime(date("Y-m-d ").$this->config->get($prefix.'_time').':00')))  {
								continue;
							}
							
							if ( in_array($shipping_method['category'], array('to-door', 'door-to-door', 'delivery-point-to-door', 'post-office')) ) {
								if ($shipping_method['category'] == 'to-door' || $shipping_method['category'] == 'delivery-point-to-door' || $shipping_method['category'] == 'door-to-door') {
									$this->session->data['shiptor']['payment'][$shipping_method['id']] = 'all_types'; // курьерская служба - все способы оплаты
								} else {
									$this->session->data['shiptor']['payment'][$shipping_method['id']] = 'cod'; // оплата наличными
								}
								$courier = false;
							} else {
								if (! isset($this->session->data['shiptor']['payment'][$shipping_method['id']])) {
									unset($this->session->data['shiptor']['payment'][$shipping_method['id']]);									
								}
								$courier = true;
							}
							
							if(trim($this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_name'))){
								$title = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_name');
							}else{
								$title = $shipping_method['name'];
							}
							
							if ($this->config->get('shipping_shiptor_days') && $value['days']) {
								$increase_days = (int)$this->config->get('shipping_shiptor_increase_days')<0?0:abs($this->config->get('shipping_shiptor_increase_days'));
								foreach(explode(' ',$value['days']) as $day){
									if(stristr($day,'-')){
										$few_days = explode('-',$day);
										$few_days[0] = (int)$few_days[0]+$increase_days;
										$few_days[1] = (int)$few_days[1]+$increase_days;
										$value['days'] = $few_days[0].'-'.$few_days[1].' '.$this->plural($few_days[1],$this->language->get('text_one_day'),$this->language->get('text_many_days'),$this->language->get('text_two_days'));
									}
									else{
										if((int)$day>0){
											$day += $increase_days;
											$value['days'] = $day.' '.$this->plural($day,$this->language->get('text_one_day'),$this->language->get('text_many_days'),$this->language->get('text_two_days'));
										}
									}
								}
								if(trim($this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_name'))){
									$title = sprintf('%s за %s', $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_name'), $value['days']);	
								}else{
									$title = sprintf('%s за %s', $shipping_method['name'], $value['days']);	
								}							
							}
							
							$comment = '';
							$this->log->write('value:' . print_r($value,true));
							if ($this->config->get($prefix . '_comment')) {
								if (isset($value['comment'])) {
									$comment = $value['comment'];
								}
							}
							else{
								if ($this->config->get($prefix . '_my_comment')) {
									$comment = $this->config->get($prefix . '_my_comment');									
								}
							}
							
							if($price<0)$price = 0;
							
							$quote_data[$shipping_method['courier'] . '-' . $shipping_method['id']] = array(
								'code'         => 'shiptor.' . $shipping_method['courier'] . '-' . $shipping_method['id'],
								'courier'      => $courier,
								'title'        => $title,
								'description'  => $comment,
								'surcharge'    => 0,								
								'cost'         => round($price + $surcharge, $this->currency->getDecimalPlace($this->session->data['currency'])),
								'tax_class_id' => 0,
								'text'         => $this->currency->format($price + $surcharge, $this->session->data['currency']),
								'category'     => $shipping_method['category'],
							);
						}
					}
				}	
			}	
		}

		$method_data = array();

		if ($quote_data) {
			
			//$quote_data_new = $this->optionAddToCostOgCod($quote_data);

			$method_data = array('code' => 'shiptor', 'title' => $this->language->get('text_title'), 'quote' => $quote_data, 'sort_order' => $this->config->get('shipping_shiptor_sort_order'), 'error' => false);
		}

		return $method_data;
	}

	private function optionAddToCostOgCod($quote_data) {
		$quote_data_new = array();

		foreach ($quote_data as $key => $val) {
			if (isset($val['cost'])) {
				$val['cost'] += $val['surcharge'];
			}

			if (isset($val['text'])) {
				$val['text'] += $val['surcharge'];
				$val['text'] .= ' р.';
			}

			$quote_data_new[$key] = $val;
		}

		return $quote_data_new;
	}

	
	// Вычисляем общий вес с учетом дефолтного значения в настройках и единиц измерения у каждого товара
	
	public function weight() {
		
		$weight = 0;
		$weightId = $this->getKgId();
		foreach ($this->cart->getProducts() as $product) {
			if (0 != $product['weight']) {
				if($this->config->get('config_weight_class_id')==$weightId)$weight += $this->weight->convert($product['weight'], $product['weight_class_id'], $weightId);
				else{
					$preWeight = $this->weight->convert($product['weight'], $product['weight_class_id'], $this->config->get('config_weight_class_id'));
					$weight += $this->weight->convert($preWeight, $this->config->get('config_weight_class_id'), $weightId);
				}
			} else {
				$weight += $product['quantity'] * $this->config->get('shipping_shiptor_weight');
			}
		}
		return $weight;
	}
	
	// получение айдишника килограммов в системе
	private function getKgId(){
		$sql = "SELECT weight_class_id FROM " . DB_PREFIX . "weight_class_description 
		WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'
		AND (unit='kg' OR unit='кг')";
		$query = $this->db->query($sql);
		return $query->row['weight_class_id'];
	}
	
	public function length($product) {
		
		$params = array();
		$lengthId = $this->getCmId();
		if($this->config->get('config_length_class_id')==$lengthId){
			$params['length'] = $this->length->convert($product['length'], $product['length_class_id'], $lengthId);
			$params['width']  = $this->length->convert($product['width'],  $product['length_class_id'], $lengthId);
			$params['height'] = $this->length->convert($product['height'], $product['length_class_id'], $lengthId);
		}
		else{
			$preLength = $this->length->convert($product['length'], $product['length_class_id'], $this->config->get('config_length_class_id'));
			$params['length'] = $this->length->convert($preLength, $this->config->get('config_length_class_id'), $lengthId);
			$preWidth = $this->length->convert($product['width'], $product['length_class_id'], $this->config->get('config_length_class_id'));
			$params['width'] = $this->length->convert($preWidth, $this->config->get('config_length_class_id'), $lengthId);
			$preHeight = $this->length->convert($product['height'], $product['length_class_id'], $this->config->get('config_length_class_id'));
			$params['height'] = $this->length->convert($preHeight, $this->config->get('config_length_class_id'), $lengthId);
		}
		return $params;
	}
	
	// получение айдишника сантиметров в системе
	private function getCmId(){
		$sql = "SELECT length_class_id FROM " . DB_PREFIX . "length_class_description 
		WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'
		AND (unit='cm' OR unit='см')";
		$query = $this->db->query($sql);
		return $query->row['length_class_id'];
	}

	
	# получение данных о доставке
	private function getShippingMethod($courier_id) {
		return $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_couriers` WHERE `id` = ' . (int)$courier_id)->row;
	}

	# получение данных о регионе
	private function getRegionId($id){
		return $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_regions` WHERE `id` = "' . $this->db->escape($id) . '" ORDER BY `id` ASC')->row;
	}

	# получаем номер Клад, если покупатель зарегестрировался
	private function getAddressId($address_id){
		$customer_id = $this->customer->getId();

		$query = $this->db->query('SELECT `kladr_id` FROM `' . DB_PREFIX . 'shipping_shiptor_address` WHERE `address_id` = ' . (int)$address_id . ' AND `customer_id` = ' . (int)$customer_id);

		return $query->row ? $query->row['kladr_id'] : 0;
	}
	
	# Сохраняем для покупателя номер кладр, если номер не был найден в базе
	public function setAddressId($address_id, $kladr_id){
		$customer_id = $this->customer->getId();

		$this->db->query('INSERT INTO `' . DB_PREFIX . 'shipping_shiptor_address` SET `address_id` = ' . (int)$address_id . ', `customer_id` = ' . (int)$customer_id . ', `kladr_id` = "' . $this->db->escape($kladr_id) . '" ON DUPLICATE KEY UPDATE `kladr_id` = "' . $this->db->escape($kladr_id) . '"');
	}

	# проверка типа доставки
	public function getValidate($quote) {
		$shipping_data = array();

		$_courier    = substr(strstr($quote, '.'), 0);	
		$_courier_id = substr(strstr($_courier, '-'), 0);
		$sipping     = substr($quote, 0, -strlen($_courier));

		if ($sipping == 'shiptor') {
			$courier_id = preg_replace('/[^0-9]/', '', $_courier_id);

			$shipping_method = $this->getShippingMethod($courier_id);

			if ($shipping_method) {
				$shipping_data['courier']    = $shipping_method['courier'];
				$shipping_data['courier_id'] = $shipping_method['id'];
				
				if (($shipping_method['category'] != 'to-door') && ($shipping_method['category'] != 'door-to-door') && ($shipping_method['category'] != 'delivery-point-to-door') && ($shipping_method['category'] != 'post-office')) {
					$shipping_data['shipping'] = 'P';					
				} else {
					$shipping_data['shipping'] = 'A';
				}
			}
		}

		return $shipping_data;
	}

	public function getOrder($order_id) {
		return $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_orders` WHERE `order_id` = ' . (int)$order_id)->row;
	}
	
	/* Изменения поссылки Shiptor */
	
	public function getOrderEditShiptor($order_id) {
		return $this->db->query('SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`shipping_address_1`,
								o.`total`, o.`shipping_code`, so.`shiptor_id`, so.`delivery_point`, so.`street`, so.`house`, so.`apartment`,
								so.`shipping_method`, so.`kladr_id`, so.`time`, so.`weight`, o.`shipping_zone`, o.`shipping_city`, o.`shipping_postcode`,
								(SELECT `iso_code_2` FROM `' . DB_PREFIX . 'country` WHERE `country_id`=o.shipping_country_id) AS country
								FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
								LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
								WHERE so.`order_id` = ' . (int)$order_id)->row;
	}
	
	public function editOrder($order_id, $data){
		if (! empty($data['shiptor_id'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `shiptor_id` = ' . (int)$data['shiptor_id'] . ' WHERE `order_id` = ' . (int)$order_id);
		}
		
		if (! empty($data['shipment_id'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `shipment_id` = ' . (int)$data['shipment_id'] . ' WHERE `order_id` = ' . (int)$order_id);
		}
		
		if (! empty($data['date_shipment'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `date_shipment` = "' . $data['date_shipment'] . '" WHERE `order_id` = ' . (int)$order_id);
		}

		if (! empty($data['street'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `street` = "' . $this->db->escape($data['street']) . '" WHERE `order_id` = ' . (int)$order_id);
			$this->db->query('UPDATE `' . DB_PREFIX . 'order` SET `payment_address_1` = "' . $this->db->escape($data['street']) . '", `shipping_address_1` = "' . $this->db->escape($data['street']) . '" WHERE `order_id` = ' . (int)$order_id);
		}
		
		if (! empty($data['house'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `house` = "' . $this->db->escape($data['house']) . '" WHERE `order_id` = ' . (int)$order_id);
		}
		
		if (! empty($data['apartment'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `apartment` = "' . $this->db->escape($data['apartment']) . '" WHERE `order_id` = ' . (int)$order_id);
		}

		if (! empty($data['weight'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `weight` = ' . (float)$data['weight'] . ' WHERE `order_id` = ' . (int)$order_id);
		}

		if (isset($data['time'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `time` = ' . (int)$data['time'] . ' WHERE `order_id` = ' . (int)$order_id);
		}

		if (! empty($data['kladr_id'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_orders` SET `kladr_id` = "' . $this->db->escape($data['kladr_id']) . '" WHERE `order_id` = ' . (int)$order_id);
		}		

		if (! empty($data['telephone'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'order` SET `telephone` = "' . $this->db->escape($data['telephone']) . '" WHERE `order_id` = ' . (int)$order_id);
		}

		if (! empty($data['email'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'order` SET `email` = "' . $this->db->escape($data['email']) . '" WHERE `order_id` = ' . (int)$order_id);
		}
	}
	
	public function productsOrder($order_id) {
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'order_product` WHERE `order_id` = ' . (int)$order_id);

		return $query->rows;
	}
	
	public function totalOrder($order_id) {
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'order_total` WHERE `order_id` = ' . (int)$order_id . ' AND `code` = "coupon"');

		return $query->row;
	}
	
	public function getCoupon($code) {
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'coupon` WHERE `code` = ' . $code);

		return $query->row;
	}

	/* Изменения поссылки Shiptor */

	# Записываем данные заказа
	public function setOrder($order_id) {

		$weight = $this->weight();

		# Адрес пункта самовывоза
		if (isset($this->session->data['shiptor']['address']['point'])) {
			$this->db->query('UPDATE `' . DB_PREFIX . 'order` SET `payment_address_1` =  "' . $this->db->escape($this->session->data['shiptor']['address']['point']) . '", `shipping_address_1` = "' . $this->db->escape($this->session->data['shiptor']['address']['point']) . '" WHERE `order_id` = ' . (int)$order_id);
		}

		$sql = 'INSERT INTO `' . DB_PREFIX . 'shipping_shiptor_orders` SET `order_id` = ' . (int)$order_id;

		# ID способа доставки
		if (isset($this->session->data['shiptor']['address']['courier_id'])) {
			$sql .= ', `shipping_method` = ' . (int)$this->session->data['shiptor']['address']['courier_id'];
		}

		# ID пункта самовывоза
		if (isset($this->session->data['shiptor']['address']['point_id'])) {		
			$sql .= ', `delivery_point` = ' . (int)$this->session->data['shiptor']['address']['point_id'];
		}

		# Адрес пункта самовывоза
		if (isset($this->session->data['shiptor']['address']['point'])) {
			$sql .= ', `address` = "' . $this->db->escape($this->session->data['shiptor']['address']['point']) . '"';
		}

		# Код КЛАДР населенного пункта, можно получить из справочника населенных пунктов
		if (isset($this->session->data['shiptor']['kladr_id'])) {	
			$sql .= ', `kladr_id` = "' . $this->db->escape($this->session->data['shiptor']['kladr_id']) . '"';
		}

		# Время доставки
		if (isset($this->session->data['shiptor']['terms'])) {
			$sql .= ', `time` = ' . (int)$this->session->data['shiptor']['terms'];
		}

		# Вес
		if ($weight) {
			$sql .= ', `weight` = ' . (float)$weight;
		}

		$sql .= ' ON DUPLICATE KEY UPDATE `order_id` = ' . (int)$order_id;

		if (isset($this->session->data['shiptor']['address']['courier_id'])) {
			$sql .= ', `shipping_method` = ' . (int)$this->session->data['shiptor']['address']['courier_id'];
		} else {
			$sql .= ', `shipping_method` = 0';
		}

		if (isset($this->session->data['shiptor']['address']['point_id'])) {		
			$sql .= ', `delivery_point` = ' . (int)$this->session->data['shiptor']['address']['point_id'];
		} else {
			$sql .= ', `delivery_point` = 0';
		}

		if (isset($this->session->data['shiptor']['address']['point'])) {
			$sql .= ', `address` = "' . $this->db->escape($this->session->data['shiptor']['address']['point']) . '"';
		} else {
			$sql .= ', `address` = ""';
		}

		if (isset($this->session->data['shiptor']['kladr_id'])) {	
			$sql .= ', `kladr_id` = "' . $this->db->escape($this->session->data['shiptor']['kladr_id']) . '"';
		} else {
			$sql .= ', `kladr_id` = ""';
		}

		if (isset($this->session->data['shiptor']['terms'])) {
			$sql .= ', `time` = ' . (int)$this->session->data['shiptor']['terms'];
		} else {
			$sql .= ', `time` = 0';
		}

		if ($weight) {
			$sql .= ', `weight` = ' . (float)$weight;
		} else {
			$sql .= ', `weight` = 0';
		}

		$this->db->query($sql);	
	}
	
	// правильное окончание в зависимости от количества
	private function plural($number, $singular, $plural1, $plural2=null){
		
		$number = abs($number); 
		if(!empty($plural2)){
			$p1 = $number%10;
			$p2 = $number%100;
			if($number == 0)
				return $plural1;
			if($p1==1 && !($p2>=11 && $p2<=19))
				return $singular;
			elseif($p1>=2 && $p1<=4 && !($p2>=11 && $p2<=19))
				return $plural2;
			else
				return $plural1;
		}
		else{
			if($number == 1)
				return $singular;
			else
				return $plural1;
		}
	}
	
	// определение населённого пункта из подсказок, смотря на регион/страну
	private function getSuggest($address = array()){
		
		if(!empty($address['zone_id'])){
			$this->load->model('localisation/zone');
			$zone = $this->model_localisation_zone->getZone($address['zone_id']);
			$zone = mb_strtolower($zone['name']);
			$zone = trim(str_replace($this->cleanAreas,'',$zone));
		}
		$results = $this->shiptor->suggestSettlement(array('query' => trim($address['city']),'country_code'=>$address['iso_code_2']));
		$searchCity = strtolower($address['city']);
		$result = false;
		foreach($results as $suggest){
			if($result==false && $searchCity==strtolower($suggest['name'])){
				if($suggest['readable_parents']!=''){
					$area = mb_strtolower($suggest['readable_parents']);
					$area = trim(str_replace($this->cleanAreas,'',$area));
					$area = explode(',',$area);
					
					if(isset($zone) && trim($area[0])==$zone)return $suggest;
					if(isset($area[1])) if(isset($zone) && trim($area[1])==$zone)return $suggest;
				}
				else {
					if(isset($zone) && trim(mb_strtolower($suggest['name']))==$zone)return $suggest;
					else {
						if(!isset($zone))return $suggest;
					}
				}
			}
		}
		return $result;
	}
	
	public function getKladr(){
		
		$cleanAreas = array('город','республика','область','ао','край','автономный','округ','г.','обл.');
		$results = $params = array();
		$country_id = isset($this->session->data['shipping_address']['country_id'])?$this->session->data['shipping_address']['country_id']:($this->config->get('config_country_id')!=0?$this->config->get('config_country_id'):0);
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
		
		$params['query'] = isset($this->session->data['shipping_address']['city'])?trim($this->session->data['shipping_address']['city']):'';
		$suggests = $this->shiptor->suggestSettlement($params);
			
		if($suggests){
			foreach($suggests as $i=>$result){
				if(!empty($zones)){
					//if($result['name'] == $this->session->data['shipping_address']['city']){
						$results[$i] = $result;
						$area = mb_strtolower($result['readable_parents']);
						$area = trim(str_replace($cleanAreas,'',$area));
						$area = explode(',',$area);
						$results[$i]['zone_id'] = array_search(trim($area[0]),$zones);
						if(!$results[$i]['zone_id'] && isset($area[1])) $results[$i]['zone_id'] = array_search(trim($area[1]),$zones);
						$results[$i]['zone_id'] = array_search(trim(mb_strtolower($result['name'])),$zones);
					//} 
				}
			}
		}
		
		return $results;
	}
	
	public function updateAddressId($address_id, $kladr_id){
		$customer_id = $this->customer->getId();
		
		$this->db->query('UPDATE `' . DB_PREFIX . 'shipping_shiptor_address` SET `address_id` = ' . (int)$address_id . ', `customer_id` = ' . (int)$customer_id . ', `kladr_id` = "' . $this->db->escape($kladr_id) . '" WHERE `address_id` = "' . (int)$address_id . '" AND `customer_id` = "' . (int)$customer_id . '"');
	}
				
				
	public function updateTimeAuto($store_id = 0, $code = 'shipping_shiptor', $key = 'shipping_shiptor_time_auto'){
		$time_auto = time() + 120;
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND  `key` = '" . $this->db->escape($key) . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($time_auto) . "'");
	}
	
	public function productOptionsOrder($order_id, $product_id) {
		$query = $this->db->query('SELECT oo.*, pov.option_value_id FROM `' . DB_PREFIX . 'order_option` oo LEFT JOIN `' . DB_PREFIX . 'product_option_value` pov ON(oo.`product_option_value_id` = pov.`product_option_value_id`) WHERE oo.`order_id` = "' . (int)$order_id . '" AND oo.`order_product_id` =  "' . $product_id . '" ORDER BY pov.`option_id`');

		return $query->rows;
	}
	
	public function getDateOrders($valid_period) {
		$second_start = time() - $valid_period;
		$start_date = date('Y-m-d H:i:s',$second_start);
		$end_date = date('Y-m-d H:i:s',time());
		
		$sql = 'SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`payment_method`, o.`shipping_address_1`, o.`shipping_code`, o.`shipping_zone`, o.`shipping_postcode`,
		o.`total`, o.`date_added`, so.`shiptor_id`, so.`delivery_point`, so.`address`, so.`street`, so.`house`, so.`apartment`, so.`shipping_method`, (SELECT `iso_code_2` FROM `' . DB_PREFIX . 'country` WHERE `country_id`=o.shipping_country_id) AS country,
		so.`kladr_id`, so.`time`, so.`weight`, o.`order_status_id`, os.`name` AS status, o.`shipping_city`
		FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
		LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
		LEFT JOIN `' . DB_PREFIX . 'order_status` os ON (o.`order_status_id` = os.`order_status_id`)
		WHERE 1 = 1 AND o.`order_status_id` = "' . $this->config->get('shipping_shiptor_order_status_for_api') . '" AND so.`shiptor_id`=0 AND o.`date_added` BETWEEN "' . $start_date . '" AND"' . $end_date . '"';
		
		$sql .= ' GROUP BY o.`order_id` ORDER BY o.`order_id` DESC, o.`date_added` DESC';
		
		$query = $this->db->query($sql);
		$order_periods = array();
		
		foreach($query->rows as $row){
			$shipping_method = $this->getShippingMethod($row['shipping_method']);
			if(!empty($shipping_method)){
				if((stristr($shipping_method['category'],'-to-') == false)){
					$order_periods[] = array(
						'order_id'				=> $row['order_id'],
						'firstname'				=> $row['firstname'],
						'lastname'				=> $row['lastname'],
						'email'					=> $row['email'],
						'telephone'				=> $row['telephone'],
						'payment_code'			=> $row['payment_code'],
						'payment_method'		=> $row['payment_method'],
						'shipping_address_1'	=> $row['shipping_address_1'],
						'shipping_code'			=> $row['shipping_code'],
						'shipping_zone'			=> $row['shipping_zone'],
						'country'				=> $row['country'],
						'total'					=> $row['total'],
						'date_added'			=> $row['date_added'],
						'shiptor_id'			=> $row['shiptor_id'],
						'delivery_point'		=> $row['delivery_point'],
						'street'				=> $row['street'],
						'house'					=> $row['house'],
						'apartment'				=> $row['apartment'],
						'shipping_postcode'		=> $row['shipping_postcode'],
						'shipping_method'		=> $row['shipping_method'],
						'kladr_id'				=> $row['kladr_id'],
						'time'					=> $row['time'],
						'weight'				=> $row['weight'],
						'order_status_id'		=> $row['order_status_id'],
						'status'				=> $row['status'],
						'shipping_city'			=> $row['shipping_city'],
					);
				}
			}
		}

		return $order_periods;
	}
	
	public function getPvz($type,$courier) {
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_couriers` WHERE category = "' . $type . '" AND courier = "' . $courier . '"');

		return $query->row;
	}
	
	public function getDateOrdersCourierGroup($valid_period) {
		$second_start = time() - $valid_period;
		$start_date = date('Y-m-d H:i:s',$second_start);
		$end_date = date('Y-m-d H:i:s',time());
		
		$sql = 'SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`payment_method`, o.`shipping_address_1`, o.`shipping_code`, o.`shipping_zone`, o.`shipping_postcode`,
		o.`total`, o.`date_added`, so.`shiptor_id`, so.`delivery_point`, so.`address`, so.`street`, so.`house`, so.`apartment`, so.`shipping_method`, (SELECT `iso_code_2` FROM `' . DB_PREFIX . 'country` WHERE `country_id`=o.shipping_country_id) AS country,
		so.`kladr_id`, so.`time`, so.`weight`, o.`order_status_id`, os.`name` AS status, o.`shipping_city`
		FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
		LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
		LEFT JOIN `' . DB_PREFIX . 'order_status` os ON (o.`order_status_id` = os.`order_status_id`)
		WHERE 1 = 1 AND o.`order_status_id` = "' . $this->config->get('shipping_shiptor_order_status_for_api') . '" AND so.`shiptor_id`=0 AND o.`date_added` BETWEEN "' . $start_date . '" AND"' . $end_date . '"';
		
		$sql .= ' GROUP BY o.`order_id` ORDER BY o.`order_id` DESC, o.`date_added` DESC';
		
		$query = $this->db->query($sql);
		$order_periods = array();
		
		foreach($query->rows as $row){
			$shipping_method = $this->getShippingMethod($row['shipping_method']);
			if(!empty($shipping_method)){
				if((stristr($shipping_method['category'],'-to-'))){
					if($shipping_method['category'] == 'door-to-delivery-point' || $shipping_method['category'] == 'door-to-door'){
						$type = 'courier';
					}elseif($shipping_method['category'] == 'delivery-point-to-delivery-poi' || $shipping_method['category'] == 'delivery-point-to-door'){
						$type = 'delivery-point';
					}
					$order_periods[$type . '.' . $shipping_method['courier']][] = array(
						'order_id'				=> $row['order_id'],
						'firstname'				=> $row['firstname'],
						'lastname'				=> $row['lastname'],
						'email'					=> $row['email'],
						'telephone'				=> $row['telephone'],
						'payment_code'			=> $row['payment_code'],
						'payment_method'		=> $row['payment_method'],
						'shipping_address_1'	=> $row['shipping_address_1'],
						'shipping_code'			=> $row['shipping_code'],
						'shipping_zone'			=> $row['shipping_zone'],
						'country'				=> $row['country'],
						'total'					=> $row['total'],
						'date_added'			=> $row['date_added'],
						'shiptor_id'			=> $row['shiptor_id'],
						'delivery_point'		=> $row['delivery_point'],
						'street'				=> $row['street'],
						'house'					=> $row['house'],
						'apartment'				=> $row['apartment'],
						'shipping_postcode'		=> $row['shipping_postcode'],
						'shipping_method'		=> $row['shipping_method'],
						'kladr_id'				=> $row['kladr_id'],
						'time'					=> $row['time'],
						'weight'				=> $row['weight'],
						'order_status_id'		=> $row['order_status_id'],
						'status'				=> $row['status'],
						'shipping_city'			=> $row['shipping_city'],
					);
				}
			}
		}

		return $this->shiptor_courier($order_periods);
	}
	
	public function shiptor_courier($orders){
		if(!empty($orders)){
			foreach($orders as $key => $shipping) {
				list($type,$courier) = explode('.', $key);
				
				$params = array();
				
				$params['shipment']['type'] = $type;
				$params['shipment']['courier'] = $courier;
				
				if($this->config->get('shipping_shiptor_fio_delivery')){
					$params['shipment']['address']['receiver'] = $this->config->get('shipping_shiptor_fio_delivery');
				}else{
					$params['shipment']['address']['receiver'] = $this->config->get('config_owner');
				}
				
				if($this->config->get('shipping_shiptor_email_delivery')){
					$email = utf8_strtolower(trim($this->config->get('shipping_shiptor_email_delivery')));
				}else{
					$email = utf8_strtolower(trim($this->config->get('config_email')));
				}
				
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$params['shipment']['address']['email'] = $email;
				}else {
					$json['error']['email'] = $this->language->get('error_is_email');
				}
				
				if($this->config->get('shipping_shiptor_telephone_delivery')){
					if (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui', $this->config->get('shipping_shiptor_telephone_delivery'))) {
						$params['shipment']['address']['phone'] = $this->config->get('shipping_shiptor_telephone_delivery');
					}
				}elseif (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui', $this->config->get('config_telephone'))) {
					$params['shipment']['address']['phone'] = $this->config->get('config_telephone');
				}else{
					$json['error']['telephone'] = $this->language->get('error_is_telephone');
				}
				
				if($this->config->get('config_country_id')){
					$this->load->model('localisation/country');

					$country = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
					$params['shipment']['address']['country'] = $country['iso_code_2'];
				}else{
					$json['error']['country'] = $this->language->get('error_is_county');
				}
				
				if($this->config->get('shipping_shiptor_city_delivery')){
					$params['shipment']['address']['settlement'] = $this->config->get('shipping_shiptor_city_delivery');
				}else{
					$json['error']['city'] = $this->language->get('error_is_city');
				}
				
				if($this->config->get('shipping_shiptor_address_delivery')) {
					$params['shipment']['address']['street'] = $this->config->get('shipping_shiptor_address_delivery');
				}else{
					$json['error']['address'] = $this->language->get('error_is_address');
				}
				
				if($this->config->get('shipping_shiptor_city_delivery_value')){
					$params['shipment']['address']['kladr_id'] = $this->config->get('shipping_shiptor_city_delivery_value');
				}else{
					$json['error']['kladr_id'] = $this->language->get('error_is_kladr');
				}
				
				if($params['shipment']['type'] == 'delivery-point'){
					$shipping_param = $this->getPvz('delivery-point-to-door',$courier);
					if ($this->config->get('shipping_shiptor_'. $shipping_param['id'] . '_pvz')){
						$params['shipment']['delivery_point'] = (int)$this->config->get('shipping_shiptor_'. $shipping_param['id'] . '_pvz');
					}else{
						$json['error']['delivery_point'] = $this->language->get('error_is_delivery_point');
					}
				}
				
				if($this->config->get('shipping_shiptor_postponement_delivery')){
					$date_shipment_for_array = time() + $this->config->get('shipping_shiptor_postponement_delivery');
					$date_shipment = time() + $this->config->get('shipping_shiptor_postponement_delivery');
					$dt['date_shipment'] = $date_shipment + 604800;
					$dt['date_shipment_from'] = time();
					$shiptor = $this->shiptor->getDaysOffShipment($dt);
					if(!empty($shiptor)){
						$i = 0;
						foreach($shiptor as $date){
							if(date('Y-m-d', $date_shipment_for_array) == $date){
								$date_shipment_for_array += 86400;
								$i++;
							}
						}
						$time_shiptor = $i * 86400;
						$date_shipment += $time_shiptor;
					}
					
					// Дата отправления (формат d.m.Y)
					$params['shipment']['date'] = date("d.m.Y", $date_shipment);
				}else{
					$json['error']['date'] = $this->language->get('error_is_date');
				}
			
				// PACKAGE olso
				$params['packages'] = array();
				
				foreach($shipping as $key => $order){
					$params['packages'][$key]['external_id'] = $order['order_id'];
					
					$params['packages'][$key]['length'] = 1;
					
					if ($this->config->get('shipping_shiptor_default_length')) {
						$params['packages'][$key]['length'] = (float)$this->config->get('shipping_shiptor_default_length');
					}
					
					$params['packages'][$key]['width'] = 1;
					
					if ($this->config->get('shipping_shiptor_default_width')) {
						$params['packages'][$key]['width'] = (float)$this->config->get('shipping_shiptor_default_width');
					}
					
					$params['packages'][$key]['height'] = 1;
					
					if ($this->config->get('shipping_shiptor_default_height')) {
						$params['packages'][$key]['height'] = (float)$this->config->get('shipping_shiptor_default_height');
					}
					
					$weight = trim($order['weight']);

					if ($weight && is_numeric($weight)) {
						$params['packages'][$key]['weight'] = (float)$weight;
					}else{
					
						$params['packages'][$key]['weight'] = 1;
						
						if ($this->config->get('shipping_shiptor_default_weight')) {
							$params['packages'][$key]['weight'] = (float)$this->config->get('shipping_shiptor_default_weight');
						}
					}

					
					$shipping_method = trim($order['shipping_method']);

					if ($shipping_method && is_numeric($shipping_method)) {
						$params['packages'][$key]['departure']['shipping_method'] = (int)$shipping_method;
					} else {
						$json['error']['shipping'] = $this->language->get('error_is_shipping');
					}
					
					if(stristr($order['shipping_code'],'shiptor.russian-post-') && $order['lastname']==''){
						$json['error']['shipping'] = $this->language->get('error_is_surname');
					}

					$delivery_point = trim($order['delivery_point']);

					if ($delivery_point) {
						$params['packages'][$key]['departure']['delivery_point'] = $delivery_point;
					} else {
						
						if ($order['street']) {
							$params['packages'][$key]['departure']['address']['address_line_1'] = trim($order['street']);
						} else if ($order['shipping_address_1']) {
							$this->editOrder($order['order_id'], array('street' => trim($order['shipping_address_1'])));

							$params['packages'][$key]['departure']['address']['address_line_1'] = trim($order['shipping_address_1']);
						}

						if ($order['apartment']) {
							$params['packages'][$key]['departure']['address']['apartment'] = trim($order['apartment']);
						}
						
						if ($order['shipping_postcode']) {
							$params['packages'][$key]['departure']['address']['postal_code'] = trim($order['shipping_postcode']);
						}
					}

					if ($order['firstname']) {
						$params['packages'][$key]['departure']['address']['name'] = $order['firstname'];
					}

					if ($order['lastname']) {
						$params['packages'][$key]['departure']['address']['surname'] =  $order['lastname']!=''?$order['lastname']:$order['firstname'];
					}
					else{
						$params['packages'][$key]['departure']['address']['surname'] = $order['firstname'];
					}

					$email = utf8_strtolower(trim($order['email']));

					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$params['packages'][$key]['departure']['address']['email'] = $email;
					} else {
						$json['error']['email'] = $this->language->get('error_is_email');
					}

					if ($order['telephone']) {
						if (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui', $order['telephone'])) {
							$params['packages'][$key]['departure']['address']['phone'] = trim($order['telephone']);
						} else {
							$json['error']['telephone'] = $this->language->get('error_is_telephone');
						}
					}

					$kladr_id = trim($order['kladr_id']);

					if ($kladr_id) {
						$params['packages'][$key]['departure']['address']['kladr_id'] = $kladr_id;
					} else {
						$json['error']['kladr_id'] = $this->language->get('error_is_kladr');
					}

					// вычисляем наложенный платеж и объявленную стоимость
					$params['packages'][$key]['cod'] = 0;
					$params['packages'][$key]['declared_cost'] = 10;
					
					if ($order['total']) {
						$params['packages'][$key]['declared_cost'] = round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency')));

						$params['packages'][$key]['cod'] = (false !== strpos($order['payment_code'], 'shiptor_')) ? round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency'))) : 0;
					
					}
					
					if($params['packages'][$key]['declared_cost'] >= 10){
						$params['packages'][$key]['declared_cost'] = $params['packages'][$key]['declared_cost'];
					}else{
						$params['packages'][$key]['declared_cost'] = 10;
					}

					if ($order['payment_code'] == 'shiptor_paycard') {
						$params['packages'][$key]['departure']['cashless_payment'] = true;
					}
					
					// Пока отправляем только RU
					if ($order['country']) {
						$params['packages'][$key]['departure']['address']['country'] = $order['country'];
					}
					
					if ($order['shipping_zone']) {
						$params['packages'][$key]['departure']['address']['administrative_area'] = $order['shipping_zone'];
					}
					
					if ($order['shipping_city']) {
						$params['packages'][$key]['departure']['address']['settlement'] = $order['shipping_city'];
					}

					// проверка наличия услуги с артикулом магазина (если нет - создаем)
					// артикул = url сайта

					$shop_article = mb_substr(HTTPS_CATALOG, 0, 64);

					if (!$this->shiptor->checkServiceByArticle($shop_article)) {
						$data = array('name' => $this->language->get('text_shipping'), 'shopArticle' => $shop_article, 'price' => 0);
						$this->shiptor->addService($data);
					}

					if ($products = $this->productsOrder($order['order_id'])) {
						$this->load->model('catalog/product');

						// передаем номенклатуру

						foreach ($products as $product) {
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);

							// TODO: проверить, откуда данные - из product или product_info
							$shopArticle = '';
							$name = $product['name'];
							
							if(($this->config->get('shipping_shiptor_id_product')) && (!empty($product_info['sku']))){
								$shopArticle = $product_info['sku'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
										$name .= '-' . $option['value'];
									}
								}
							}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
								$shopArticle = $product['model'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
										$name .= '-' . $option['value'];
									}
								}
							}elseif(!empty($product['sku'])){
								$shopArticle = $product_info['sku'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
										$name .= '-' . $option['value'];
									}
								}
							}else{
								$shopArticle = $product['model'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
										$name .= '-' . $option['value'];
									}
								}
							}

							$product_shiptor_params = array(
								'name'          => $name,
								'article'       => empty($product['sku']) ? '' : $product['sku'],
								'shopArticle'   => $shopArticle,
								'length'		=> empty($product['length']) ? '' : $this->length->convert($product['length'], 1, 1),
								'width'         => empty($product['width']) ? '' : $this->length->convert($product['width'],  1, 1),
								'height'        => empty($product['height']) ? '' : $this->length->convert($product['height'], 1, 1),
								'weight'        => empty($product['weight']) ? '' : $this->length->convert($product['weight'], 1, 1),
								'price'         => round($product['price'], $this->currency->getDecimalPlace($this->config->get('config_currency'))),
							);

							$this->shiptor->addProduct($product_shiptor_params);
						}

						// если товар один, и указаны его размеры - берем их
						// если товар один, и размеров нет - ставим нули, в библиотеке подставятся дефолтные размеры
						// если товаров несколько - пропускаем блок, в библиотеке подставятся дефолтные размеры

						if (1 == sizeof($products)) {
							$product = reset($products);

							if (1 == $product['quantity']) {
								$product_info = $this->model_catalog_product->getProduct($product['product_id']);
								$params['packages'][$key] = array_merge($params['packages'][$key],$this->length($product_info));
							}
						}
						
						if ($params['packages'][$key]['length'] > 0) {
							$params['packages'][$key]['length'] = (int)$params['packages'][$key]['length'];
						}elseif ($this->config->get('shipping_shiptor_default_length')) {
							$params['packages'][$key]['length'] = (int)$this->config->get('shipping_shiptor_default_length');
						}else{
							$params['packages'][$key]['length'] = 1;
						}
						
						if ($params['packages'][$key]['width'] > 0) {
							$params['packages'][$key]['width'] = (int)$params['packages'][$key]['width'];
						}elseif ($this->config->get('shipping_shiptor_default_width')) {
							$params['packages'][$key]['width'] = (int)$this->config->get('shipping_shiptor_default_width');
						}else{
							$params['packages'][$key]['width'] = 1;
						}
						
						if ($params['packages'][$key]['height'] > 0) {
							$params['packages'][$key]['height'] = (int)$params['packages'][$key]['height'];
						}elseif ($this->config->get('shipping_shiptor_default_height')) {
							$params['packages'][$key]['height'] = (int)$this->config->get('shipping_shiptor_default_height');
						}else{
							$params['packages'][$key]['height'] = 1;
						}

						# Cписок продуктов
						$data['products'] = array();
						$params['packages'][$key]['products'] = array();

						$costProduct = 0;
						
						// Coupon
						$order_total = $this->totalOrder($order['order_id']);
						if(!empty($order_total)){
							preg_match('#\((.*?)\)#', $order_total['title'], $title);
							$coupon_info = $this->getCoupon($title[1]);
							$discount = (int)$coupon_info['discount'];
						}else{
							$coupon_info = array();
						}
						
						foreach ($products as $product) {
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);
							$shopArticle = '';
							$price = $product['price'];
							if(!empty($coupon_info) && $discount > 0){
								if($coupon_info['type'] == 'P'){
									$price = $price*$discount/100;
								}elseif($coupon_info['type'] == 'F'){
									if($product['quantity'] > 1){
										if(($price*$product['quantity']) > $discount){
											$price = (($price*$product['quantity']) - $discount)/$product['quantity'];
											$discount = 0;
										}else{
											$discount = $discount - ($price*$product['quantity']);
											$price = 0;
										}
									}else{
										if($price > $discount){
											$price = $price - $discount;
											$discount = 0;
										}else{
											$discount = $discount - $price;
											$price = 0;
										}
									}
								}
							}
							
							if(($this->config->get('shipping_shiptor_id_product')) && (!empty($product_info['sku']))){
								$shopArticle = $product_info['sku'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
									}
								}
							}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
								$shopArticle = $product['model'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
									}
								}
							}elseif(!empty($product['sku'])){
								$shopArticle = $product_info['sku'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
									}
								}
							}else{
								$shopArticle = $product['model'];
								$product_options = $this->productOptionsOrder($order['order_id'], $product['order_product_id']);
								if(!empty($product_options)){
									foreach($product_options as $option){
										$shopArticle .= '-' . $option['value'];
									}
								}
							}
							
							if (empty($product['quantity'])) {
								$json['error']['product'] = $this->language->get('error_is_quantity');
								continue;
							}

							if (empty($product['price'])) {
								$json['error']['product'] = $this->language->get('error_is_price');
								continue;
							}

							$costProduct += (int)$product['quantity']*round($price, $this->currency->getDecimalPlace($this->config->get('config_currency')));
							$costProduct = round($costProduct, 0);
							
							$params['packages'][$key]['products'][] = array(
								'shopArticle' => $shopArticle,
								'count' 	  => $product['quantity'],
								'price' 	  =>  round($price, $this->currency->getDecimalPlace($this->config->get('config_currency')))
							);
						}
						
						if (0 != $params['packages'][$key]['cod']) {
							$params['packages'][$key]['services'][] = array(
								'shopArticle'	=> mb_substr(HTTPS_CATALOG, 0, 64), // артикул услуги = url сайта (к этому моменту уже создана в ЛК шиптора)
								'price'		=> round($params['packages'][$key]['cod']  - $costProduct, $this->currency->getDecimalPlace($this->config->get('config_currency'))),
								'count'		=> 1,
							);
						}
					}
				}
				
				if (!isset($json)) {
					$shiptor = $this->shiptor->addPackages($params);
					if($shiptor){
						$db_date = date('Y-m-d', strtotime($params['shipment']['date']));
						
						foreach($shiptor['packages'] as $shiptor_s){
							if (! empty($shiptor_s['id'])) {
								$this->editOrder($shiptor_s['external_id'], array('shiptor_id' => $shiptor_s['id'],'shipment_id' => $shiptor['shipment']['id'], 'date_shipment' => $db_date));
								if($shiptor_s['status'] == 'new' && $this->config->get('shipping_shiptor_order_status_after_api_new') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_new'));
								}elseif($shiptor_s['status'] == 'in_store' && $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse'));
								}elseif($shiptor_s['status'] == 'packed' && $this->config->get('shipping_shiptor_order_status_after_api_packed') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_packed'));
								}elseif($shiptor_s['status'] == 'sent' && $this->config->get('shipping_shiptor_order_status_after_api_sent') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_sent'));
								}elseif($shiptor_s['status'] == 'delivered' && $this->config->get('shipping_shiptor_order_status_after_api_delivered') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_delivered'));
								}elseif($shiptor_s['status'] == 'returned_to_warehouse' && $this->config->get('shipping_shiptor_order_status_after_api_returned_to_warehouse') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_returned_to_warehouse'));
								}elseif($shiptor_s['status'] == 'resend' && $this->config->get('shipping_shiptor_order_status_after_api_resend') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_resend'));
								}elseif($shiptor_s['status'] == 'to_return' && $this->config->get('shipping_shiptor_order_status_after_api_to_return') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_to_return'));
								}elseif($shiptor_s['status'] == 'on_assemble' && $this->config->get('shipping_shiptor_order_status_after_api_on_assemble') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_on_assemble'));
								}elseif($shiptor_s['status'] == 'removed' && $this->config->get('shipping_shiptor_order_status_after_api_removed') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_removed'));
								}elseif($shiptor_s['status'] == 'lost' && $this->config->get('shipping_shiptor_order_status_after_api_lost') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_lost'));
								}elseif($shiptor_s['status'] == 'to_disband' && $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_disband') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_disband'));
								}elseif($shiptor_s['status'] == 'wdisbanded' && $this->config->get('shipping_shiptor_order_status_after_api_disbanded') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_disbanded'));
								}elseif($shiptor_s['status'] == 'reported' && $this->config->get('shipping_shiptor_order_status_after_api_pickup') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_pickup'));
								}elseif($shiptor_s['status'] == 'returned' && $this->config->get('shipping_shiptor_order_status_after_api_returned') > 0){
									$this->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_returned'));
								}
							}
						}		
					}
				}
			}
		}
	}
	
	public function addOrderHistory($order_id, $order_status_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '0', comment = 'API Shiptor', date_added = NOW()");
	}
}