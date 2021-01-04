<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ModelExtensionPaymentShiptorPay extends Model {
	public function getMethod($address, $total) {
		
		$method_data = array();
		$status = false;
		$this->load->model('extension/shipping/shiptor');
		$country = 'RU';
		if(isset($this->session->data['simple']['shipping_address']['iso_code_2'])){
			$country = $this->session->data['simple']['shipping_address']['iso_code_2'];
		}elseif(isset($this->session->data['shipping_address']['iso_code_2'])){
			$country = $this->session->data['shipping_address']['iso_code_2'];
		}elseif(isset($this->session->data['simple']['payment_address']['iso_code_2'])){
			$country = $this->session->data['simple']['payment_address']['iso_code_2'];
		}elseif(isset($this->session->data['payment_address']['iso_code_2'])){
			$country = $this->session->data['payment_address']['iso_code_2'];
		}
		
		if($country == 'RU'){
			if (isset($this->session->data['shipping_method']['code'])) {
				if ($shipping = $this->model_extension_shipping_shiptor->getValidate($this->session->data['shipping_method']['code'])) {
					if (isset($this->session->data['shiptor']['payment'][$shipping['courier_id']])) {
						if (
							($shipping['shipping'] == 'A')
							||
							(($this->session->data['shiptor']['payment'][$shipping['courier_id']] === 'all_types') || ($this->session->data['shiptor']['payment'][$shipping['courier_id']] === 'cod'))
						) {
							$status = true;
						}
					}
				}
			}
			else{
				// пока перебираем только методы шиптора
				if(isset($this->session->data['shipping_methods']['shiptor'])){
					foreach($this->session->data['shipping_methods']['shiptor']['quote'] as $code=>$shipping_method){
						if($status==false){ //перебираем только первого подходящего
							$shipping = $this->model_extension_shipping_shiptor->getValidate($shipping_method['code']);
							if (!empty($shipping)) {
								if ($shipping['shipping'] == 'A' || (isset($shipping['category']) && in_array($shipping['category'], array('to-door', 'door-to-door', 'delivery-point-to-door', 'post-office')))){
									$status = true;
								}
							}
						}
					}
				}
			}
		}
		if($status==true){
			$this->load->language('extension/payment/shiptor_pay');
			$method_data = array(
				'code'		=> 'shiptor_pay',
				'title'		=> $this->language->get('text_title'),
				'terms'		=> '',
				'sort_order'	=> $this->config->get('payment_shiptor_pay_sort_order'),
			);
		}
		return $method_data;
	}
}