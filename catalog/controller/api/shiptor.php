<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ControllerApiShiptor extends Controller {
	public function __construct($registry) {
		parent::__construct($registry);
		$registry->set('shiptor', new Shiptor($registry));
	}

	public function kladr() {
		if (! empty($this->request->post['kladr_id'])) {
			$this->session->data['shiptor']['kladr_id'] = $this->request->post['kladr_id'];
		}
	}

	public function address() {
		$this->language->load('extension/shipping/shiptor');

		$json = array();
		
		$shipping_method = false;
		
		if (isset($this->request->post['shipping_method'])) {
			$shipping_method = $this->request->post['shipping_method'];
		}

		$order_id = false;

		if (isset($this->request->post['order_id'])) {
			$order_id = $this->request->post['order_id'];
		}
		
		if (! $shipping_method) {
			$json['error'] = $this->language->get('error_shipping_method');
		}
		
		if (! $order_id && !$this->request->get['api_token']) { 
			$json['error'] = $this->language->get('error_order_id');
		}
		
		// сбор данных по товарам из заказа
		
		$this->load->model('account/order');
		$this->load->model('catalog/product');
		
		$products = $this->cart->getProducts();
		
		if (! $json) {
			$this->load->model('extension/shipping/shiptor');
			
			if ($shipping = $this->model_extension_shipping_shiptor->getValidate($shipping_method)) {
				if (! isset($this->session->data['shiptor']['kladr_id'])) {
					$json['error'] = $this->language->get('error_kladr');			
				} else {
					$kladr_id = $this->session->data['shiptor']['kladr_id'];
				}
				
				if (! $json) {
					if ($shipping['shipping'] == 'P') {				
						
						$json['heading_title'] = $this->language->get('heading_point');
						
						$params = array(
							'kladr_id'		=> $kladr_id,
							'shipping_method'	=> $shipping['courier_id'],
						);

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
							foreach ($points as $key => $point) {
								if (! isset($point['cod']) && ! isset($point['card'])) {
									continue;
								}

								$json['success'][$key]['id']      = $point['id'];
								$json['success'][$key]['address'] = $point['address'];

								$json['success'][$key]['cod'] = 0;
								
								if (isset($point['cod']) && $point['cod']) {
									$json['success'][$key]['cod'] = 1;
								}	
								
								$json['success'][$key]['card'] = 0;

								if (isset($point['card']) && $point['card']) {
									$json['success'][$key]['card'] = 1;
								}

								$json['success'][$key]['courier_id'] = $shipping['courier_id'];
							}
						} else {
							$json['success'] = array(array('id' => 0, 'address' => 'Адрес доставки клиента', 'cod' => 1, 'card' => 0, 'courier_id' => $shipping['courier_id']));
						}						
					}
					if ($shipping['courier'] == 'shiptor' && $shipping['shipping'] == 'A') {
						$json['times'] = array();
						foreach($this->shiptor->getDeliveryTime() as $key=>$time){
							$json['times'][$key]['name'] = $time;
							$json['times'][$key]['cod'] = 1;
							$json['times'][$key]['card'] = 1;
							$json['times'][$key]['courier_id'] = $shipping['courier_id'];
						}
					}
				}
			}
		}
		

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function save() {
		$this->language->load('extension/shipping/shiptor');

		$json = array();		
		
		if (isset($this->request->post['shipping_method']) && preg_match('/^shiptor/i', $this->request->post['shipping_method'])) {
			if (empty($this->request->post['courier_id'])) {
				$json['error'] = 'Не определена служба доставки';
			} else {
				$this->session->data['shiptor']['address']['courier_id'] = $this->request->post['courier_id'];
				
				if (isset($this->request->post['point_id'])) {
					$this->session->data['shiptor']['address']['point_id'] = $this->request->post['point_id'];
				}
				
				if (isset($this->request->post['address'])) {
					$this->session->data['shiptor']['address']['point'] = $this->request->post['address'];
				}
				
				if (isset($this->request->post['terms'])) {
					$this->session->data['shiptor']['terms'] = $this->request->post['terms'];
				}

				if (isset($this->request->post['cod'], $this->request->post['card'])) {
					if ($this->request->post['cod'] && $this->request->post['card']) {
						$this->session->data['shiptor']['payment'][$this->request->post['courier_id']] = 'all_types';
					} else if (! $this->request->post['cod'] && $this->request->post['card']) {
						$this->session->data['shiptor']['payment'][$this->request->post['courier_id']] = 'card';
					} else if ($this->request->post['cod'] && ! $this->request->post['card']) {
						$this->session->data['shiptor']['payment'][$this->request->post['courier_id']] = 'cod';
					}
				}
				
				$json['success'] = $this->language->get('text_api_shipping_point');
			}
		}
	
		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function recalculate() {
		if (!isset($this->request->post['payment_method'])
		|| !isset($this->request->post['shipping_method'])
		|| !isset($this->session->data['shipping_address'])
		|| !isset($this->session->data['payment_methods'][$this->request->post['payment_method']]))  unset($this->session->data['shipping_method']);
		
		$json = array();
		
		// Shipping Methods
		$method_data = array();
		
		$this->session->data['payment_method']['code'] = $this->request->post['payment_method'];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);
		
				if ($quote) {
					$method_data[$result['code']] = array(
						'title'      => $quote['title'],
						'quote'      => $quote['quote'],
						'sort_order' => $quote['sort_order'],
						'error'      => $quote['error']
					);
				}
			}
		}
		
		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		$this->session->data['shipping_methods'] = $method_data;
		
		$shipping = explode('.', $this->request->post['shipping_method']);

		if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
			unset($this->session->data['shipping_method']);
		}else{
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
		}
		
		if($method_data){
			$json['shipping_methods'] = $method_data;
		}
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
