<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ControllerExtensionShippingShiptor extends Controller {
	private $error;

	public function __construct($registry) {
		parent::__construct($registry);

		$registry->set('shiptor', new Shiptor($registry));
	}

	public function index() {		
		$this->load->language('extension/shipping/shiptor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/shipping/shiptor');
		$this->load->model('localisation/weight_class');
		# Получаем списки всех методов доставки
		$shipping_methods = $this->shiptor->getShippingMethods();
		
		$this->model_extension_shipping_shiptor->showAndAlter();
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			if(count($this->request->post) == 1){
				$store_id = 0;
				$code = 'shipping_shiptor';
				$key = 'shipping_shiptor_authorization';
				
				$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND  `key` = '" . $this->db->escape($key) . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($this->request->post['shipping_shiptor_authorization']) . "'");
			}else{
				if($shipping_methods)$this->model_extension_shipping_shiptor->setShippingMethods($shipping_methods);
				$this->model_setting_setting->editSetting('shipping_shiptor', $this->request->post);
			}	

			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['heading_title']		= $this->language->get('heading_title');

		$data['text_edit']		= $this->language->get('text_edit');
		$data['text_faq']		= $this->language->get('text_faq');
		$data['text_none']		= $this->language->get('text_none');
		$data['text_enabled']		= $this->language->get('text_enabled');
		$data['text_disabled']		= $this->language->get('text_disabled');
		$data['text_none']		= $this->language->get('text_none');
		$data['text_select']		= $this->language->get('text_select');
		$data['text_loading']		= $this->language->get('text_loading');
		$data['text_order_customer']	= $this->language->get('text_order_customer');
		$data['text_order_store']	= $this->language->get('text_order_store');
		$data['text_order_shiptor']	= $this->language->get('text_order_shiptor');
		$data['text_percent']		= $this->language->get('text_percent');
		$data['text_amount']		= $this->language->get('text_amount');
		$data['text_not_use']		= $this->language->get('text_not_use');
		$data['text_article']		= $this->language->get('text_article');
		$data['text_model']			= $this->language->get('text_model');
		$data['text_size_standart']	= $this->language->get('text_size_standart');
		$data['text_size_auto']		= $this->language->get('text_size_auto');
		
		$data['tab_general']		= $this->language->get('tab_general');
		$data['tab_delivery']		= $this->language->get('tab_delivery');
		$data['tab_setting']		= $this->language->get('tab_setting');

		if ($this->config->get('shipping_shiptor_authorization')){
			$data['button_save']		= $this->language->get('button_save');
		}
		$data['button_cancel']		= $this->language->get('button_cancel');
		$data['button_authorization']	= $this->language->get('button_authorization');
		$data['button_export']			= $this->language->get('button_export');
		
		# Default settings
		$data['entry_authorization']	= $this->language->get('entry_authorization');
		$data['entry_country']		= $this->language->get('entry_country');
		$data['entry_logger']		= $this->language->get('entry_logger');
		$data['entry_region']		= $this->language->get('entry_region');

		$data['entry_validate_period']		= $this->language->get('entry_validate_period');
		$data['entry_weight']		= $this->language->get('entry_weight');
		$data['entry_weight_class_id']	= $this->language->get('entry_weight_class_id');
		$data['entry_default_dimensions'] = $this->language->get('entry_default_dimensions');
		$data['entry_status']		= $this->language->get('entry_status');
		$data['entry_sort_order']	= $this->language->get('entry_sort_order');
		$data['entry_days']		= $this->language->get('entry_days');
		$data['entry_time']		= $this->language->get('entry_time');
		$data['entry_cache']		= $this->language->get('entry_cache');
		$data['entry_comment']		= $this->language->get('entry_comment');
		$data['entry_my_comment']	= $this->language->get('entry_my_comment');
		$data['entry_product']		= $this->language->get('entry_product');
		$data['entry_order_status']		= $this->language->get('entry_order_status');
		$data['entry_all_order_status']		= $this->language->get('entry_all_order_status');
		$data['entry_order_status_for_api']		= $this->language->get('entry_order_status_for_api');
		$data['entry_order_status_after_api']		= $this->language->get('entry_order_status_after_api');
		$data['entry_auto_send']		= $this->language->get('entry_auto_send');
		$data['entry_pvz']		= $this->language->get('entry_pvz');
		$data['entry_shipping_name']		= $this->language->get('entry_shipping_name');
		$data['entry_id_product']			= $this->language->get('entry_id_product');
		$data['entry_export_product']		= $this->language->get('entry_export_product');
		
		# Statuses after
		$data['entry_order_status_after_new']							= $this->language->get('entry_order_status_after_new');
		$data['entry_order_status_after_waiting_pickup']				= $this->language->get('entry_order_status_after_waiting_pickup');
		$data['entry_order_status_after_waiting_send']					= $this->language->get('entry_order_status_after_waiting_send');
		$data['entry_order_status_after_arrived_to_warehouse']			= $this->language->get('entry_order_status_after_arrived_to_warehouse');
		$data['entry_order_status_after_packed']						= $this->language->get('entry_order_status_after_packed');
		$data['entry_order_status_after_prepared_to_send']				= $this->language->get('entry_order_status_after_prepared_to_send');
		$data['entry_order_status_after_sent']							= $this->language->get('entry_order_status_after_sent');
		$data['entry_order_status_after_delivered']						= $this->language->get('entry_order_status_after_delivered');
		$data['entry_order_status_after_waiting_on_delivery_point']		= $this->language->get('entry_order_status_after_waiting_on_delivery_point');
		$data['entry_order_status_after_removed']						= $this->language->get('entry_order_status_after_removed');
		$data['entry_order_status_after_returned']						= $this->language->get('entry_order_status_after_returned');
		$data['entry_order_status_after_reported']						= $this->language->get('entry_order_status_after_reported');
		$data['entry_order_status_after_lost']							= $this->language->get('entry_order_status_after_lost');
		$data['entry_order_status_after_resend']						= $this->language->get('entry_order_status_after_resend');
		
		if(!$shipping_methods){
			$data['help_authorization'] = $this->language->get('error_all');
		}else{
			$data['help_authorization']	= $this->language->get('help_authorization');
		}
		
		$data['help_country']		= $this->language->get('help_country');
		$data['help_region']		= $this->language->get('help_region');
		$data['help_logger']		= $this->language->get('help_logger');
		$data['help_time']		= $this->language->get('help_time');
		$data['help_comment']		= $this->language->get('help_comment');
		$data['help_my_comment']	= $this->language->get('help_my_comment');
		$data['help_product']		= $this->language->get('help_product');
		$data['help_dimensions']	= $this->language->get('help_dimensions');
		$data['help_weight_class_id']	= $this->language->get('help_weight_class_id');
		$data['help_default_dimensions'] = $this->language->get('help_default_dimensions');
		$data['help_increase_days']	= $this->language->get('help_increase_days');
		
		# Shipping settings
		$data['entry_shipping_status']	= $this->language->get('entry_shipping_status');
		$data['entry_shipping_time']	= $this->language->get('entry_shipping_time');
		$data['entry_method_shipping']	= $this->language->get('entry_method_shipping');
		
		$data['help_shipping_status']	= $this->language->get('help_shipping_status');
		$data['help_shipping_time']	= $this->language->get('help_shipping_time');
		$data['help_method_shipping']	= $this->language->get('help_method_shipping');
		
		# Region settings
		$data['entry_region_status']	= $this->language->get('entry_region_status');
		$data['entry_region_hide']	= $this->language->get('entry_region_hide');
		
		$data['help_region_status']	= $this->language->get('help_region_status');
		$data['help_region_hide']	= $this->language->get('help_region_hide');

		$data['entry_setting']		= $this->language->get('entry_setting');
		$data['entry_weight_price']	= $this->language->get('entry_weight_price');
		$data['entry_total_free']	= $this->language->get('entry_total_free');
		$data['entry_total_min']	= $this->language->get('entry_total_min');
		$data['entry_total_max']	= $this->language->get('entry_total_max');
		$data['entry_total_min_weight']	= $this->language->get('entry_total_min_weight');
		$data['entry_total_max_weight']	= $this->language->get('entry_total_max_weight');
		$data['entry_fixed']		= $this->language->get('entry_fixed');
		$data['entry_type']		= $this->language->get('entry_type');
		$data['entry_markup']		= $this->language->get('entry_markup');
		$data['entry_markup_surcharge']	= $this->language->get('entry_markup_surcharge');
		$data['entry_setting_line_delivery']	= $this->language->get('entry_setting_line_delivery');
		$data['entry_setting_city_delivery']	= $this->language->get('entry_setting_city_delivery');
		$data['entry_setting_fio_delivery']	= $this->language->get('entry_setting_fio_delivery');
		$data['entry_setting_address_delivery']	= $this->language->get('entry_setting_address_delivery');
		$data['entry_setting_telephone_delivery']	= $this->language->get('entry_setting_telephone_delivery');
		$data['entry_setting_email_delivery']	= $this->language->get('entry_setting_email_delivery');
		$data['entry_setting_postponement_delivery']	= $this->language->get('entry_setting_postponement_delivery');
		$data['entry_setting_fence_interval_delivery']	= $this->language->get('entry_setting_fence_interval_delivery');
		$data['entry_size']		= $this->language->get('entry_size');
		
		$data['help_setting']		= $this->language->get('help_setting');
		$data['help_weight_price']	= $this->language->get('help_weight_price');
		$data['help_total_free']	= $this->language->get('help_total_free');
		$data['help_total_min']		= $this->language->get('help_total_min');
		$data['help_total_max']		= $this->language->get('help_total_max');
		$data['help_total_min_weight']	= $this->language->get('help_total_min_weight');
		$data['help_total_max_weight']	= $this->language->get('help_total_max_weight');
		$data['help_fixed']		= $this->language->get('help_fixed');
		$data['help_type']		= $this->language->get('help_type');
		$data['help_markup']		= $this->language->get('help_markup');
		$data['help_markup_surcharge']	= $this->language->get('help_markup_surcharge');
		$data['help_status']		= $this->language->get('help_status');
		
		$data['error_is_time']		= $this->language->get('error_is_time');
		
		if (isset($this->error['error_authorization'])) {
			$data['error_authorization'] = $this->error['error_authorization'];
		} else {
			$data['error_authorization'] = '';
		}
		
		if (isset($this->error['error_default_weight'])) {
			$data['error_default_weight'] = $this->error['error_default_weight'];
		} else {
			$data['error_default_weight'] = '';
		}
		
		
		if (isset($this->error['error_default_dimensions'])) {
			$data['error_default_dimensions'] = $this->error['error_default_dimensions'];
		} else {
			$data['error_default_dimensions'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}		
		
		if (isset($this->session->data['warning'])) {
			$data['error'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		} else {
			$data['error'] = '';
		}

 		if (isset($this->error['warning'])) {
			$data['error'] = $this->error['warning'];
		} else {
			$data['error'] = '';
		}
		
		if (isset($this->error['error'])) {
			$data['error'] = $this->language->get('error_warning');
			
			foreach ($this->error['error'] as $name => $message) {
				$data['error_' . $name] = $message;
			}
		}

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('heading_title') . $this->language->get('text_version'), 'href' => $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true));

		$data['customer'] = $this->url->link('extension/shipping/shiptor/customer', 'user_token=' . $this->session->data['user_token'], true);
		$data['order']    = $this->url->link('extension/shipping/shiptor/order', 'user_token=' . $this->session->data['user_token'], true);
		$data['shiptor']  = $this->url->link('extension/shipping/shiptor/shiptor', 'user_token=' . $this->session->data['user_token'], true);	
		$data['action']   = $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel']   = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);
		
		$data['user_token'] = $this->session->data['user_token'];

		# Array period time
		$data['validate_periods'] = array();
		$data['validate_periods'][0]['second'] = 604800;
		$data['validate_periods'][0]['name'] = '1 Неделя';
		$data['validate_periods'][1]['second'] = 1209600;
		$data['validate_periods'][1]['name'] = '2 Недели';
		$data['validate_periods'][2]['second'] = 1814400;
		$data['validate_periods'][2]['name'] = '3 Недели';
		$data['validate_periods'][3]['second'] = 2419200;
		$data['validate_periods'][3]['name'] = '4 Недели';
		$data['validate_periods'][4]['second'] = 2629200;
		$data['validate_periods'][4]['name'] = '1 Месяц';
		$data['validate_periods'][5]['second'] = 5256126;
		$data['validate_periods'][5]['name'] = '2 Месяца';
		
		# Array postponement
		$data['postponement'] = array();
		$data['postponement'][0]['value'] = 86400;
		$data['postponement'][0]['day'] = 'Следующий день';
		$data['postponement'][1]['value'] = 172800;
		$data['postponement'][1]['day'] = '2 Дня';
		$data['postponement'][2]['value'] = 259200;
		$data['postponement'][2]['day'] = '3 Дня';

		# Default settings
		if (isset($this->request->post['shipping_shiptor_authorization'])) {
			$data['shipping_shiptor_authorization'] = $this->request->post['shipping_shiptor_authorization'];
		} else {
			$data['shipping_shiptor_authorization'] = $this->config->get('shipping_shiptor_authorization');
		}		

		if (isset($this->request->post['shipping_shiptor_logger'])) {
			$data['shipping_shiptor_logger'] = $this->request->post['shipping_shiptor_logger'];
		} else { 
			$data['shipping_shiptor_logger'] = $this->config->get('shipping_shiptor_logger');
		}

		if (isset($this->request->post['shipping_shiptor_cache'])) {
			$data['shipping_shiptor_cache'] = $this->request->post['shipping_shiptor_cache'];
		} else { 
			$data['shipping_shiptor_cache'] = $this->config->get('shipping_shiptor_cache');
		}		

		if (isset($this->request->post['shipping_shiptor_status'])) {
			$data['shipping_shiptor_status'] = $this->request->post['shipping_shiptor_status'];
		} else {
			$data['shipping_shiptor_status'] = $this->config->get('shipping_shiptor_status');
		}

		if (isset($this->request->post['shipping_shiptor_sort_order'])) {
			$data['shipping_shiptor_sort_order'] = $this->request->post['shipping_shiptor_sort_order'];
		} else { 
			$data['shipping_shiptor_sort_order'] = $this->config->get('shipping_shiptor_sort_order');
		}
		
		if (isset($this->request->post['shipping_shiptor_auto_send'])) {
			$data['shipping_shiptor_auto_send'] = $this->request->post['shipping_shiptor_auto_send'];
		} elseif ($this->config->get('shipping_shiptor_auto_send')) {
			$data['shipping_shiptor_auto_send'] = $this->config->get('shipping_shiptor_auto_send');
		} else {
			$data['shipping_shiptor_auto_send'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_size'])) {
			$data['shipping_shiptor_size'] = $this->request->post['shipping_shiptor_size'];
		} elseif ($this->config->get('shipping_shiptor_size')) {
			$data['shipping_shiptor_size'] = $this->config->get('shipping_shiptor_size');
		} else {
			$data['shipping_shiptor_size'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_id_product'])) {
			$data['shipping_shiptor_id_product'] = $this->request->post['shipping_shiptor_id_product'];
		} elseif ($this->config->get('shipping_shiptor_id_product')) {
			$data['shipping_shiptor_id_product'] = $this->config->get('shipping_shiptor_id_product');
		} else {
			$data['shipping_shiptor_id_product'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_time_auto'])) {
			$data['shipping_shiptor_time_auto'] = $this->request->post['shipping_shiptor_time_auto'];
		} elseif ($this->config->get('shipping_shiptor_time_auto')) {
			$data['shipping_shiptor_time_auto'] = $this->config->get('shipping_shiptor_time_auto');
		} else {
			$data['shipping_shiptor_time_auto'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_city_delivery'])) {
			$data['shipping_shiptor_city_delivery'] = $this->request->post['shipping_shiptor_city_delivery'];
		} elseif($this->config->get('shipping_shiptor_city_delivery')) {
			$data['shipping_shiptor_city_delivery'] = $this->config->get('shipping_shiptor_city_delivery');
		}else{
			$data['shipping_shiptor_city_delivery'] = '';
		}
		
		if (isset($this->request->post['shipping_shiptor_city_delivery_value'])) {
			$data['shipping_shiptor_city_delivery_value'] = $this->request->post['shipping_shiptor_city_delivery_value'];
		} elseif($this->config->get('shipping_shiptor_city_delivery_value')) {
			$data['shipping_shiptor_city_delivery_value'] = $this->config->get('shipping_shiptor_city_delivery_value');
		}else{
			$data['shipping_shiptor_city_delivery_value'] = '';
		}

		if (isset($this->request->post['shipping_shiptor_fio_delivery'])) {
			$data['shipping_shiptor_fio_delivery'] = $this->request->post['shipping_shiptor_fio_delivery'];
		} elseif($this->config->get('shipping_shiptor_fio_delivery')) {
			$data['shipping_shiptor_fio_delivery'] = $this->config->get('shipping_shiptor_fio_delivery');
		}else{
			$data['shipping_shiptor_fio_delivery'] = $this->config->get('config_owner');
		}
		
		if (isset($this->request->post['shipping_shiptor_address_delivery'])) {
			$data['shipping_shiptor_address_delivery'] = $this->request->post['shipping_shiptor_address_delivery'];
		} elseif($this->config->get('shipping_shiptor_address_delivery')) {
			$data['shipping_shiptor_address_delivery'] = $this->config->get('shipping_shiptor_address_delivery');
		}else{
			$data['shipping_shiptor_address_delivery'] = $this->config->get('config_address');
		}
		
		if (isset($this->request->post['shipping_shiptor_telephone_delivery'])) {
			$data['shipping_shiptor_telephone_delivery'] = $this->request->post['shipping_shiptor_telephone_delivery'];
		} elseif($this->config->get('shipping_shiptor_telephone_delivery')) {
			$data['shipping_shiptor_telephone_delivery'] = $this->config->get('shipping_shiptor_telephone_delivery');
		}else{
			$data['shipping_shiptor_telephone_delivery'] = $this->config->get('config_telephone');
		}
		
		if (isset($this->request->post['shipping_shiptor_email_delivery'])) {
			$data['shipping_shiptor_email_delivery'] = $this->request->post['shipping_shiptor_email_delivery'];
		} elseif($this->config->get('shipping_shiptor_email_delivery')) {
			$data['shipping_shiptor_email_delivery'] = $this->config->get('shipping_shiptor_email_delivery');
		}else{
			$data['shipping_shiptor_email_delivery'] = $this->config->get('config_email');
		}
		
		if (isset($this->request->post['shipping_shiptor_postponement_delivery'])) {
			$data['shipping_shiptor_postponement_delivery'] = $this->request->post['shipping_shiptor_postponement_delivery'];
		} elseif($this->config->get('shipping_shiptor_postponement_delivery')) {
			$data['shipping_shiptor_postponement_delivery'] = $this->config->get('shipping_shiptor_postponement_delivery');
		}else{
			$data['shipping_shiptor_postponement_delivery'] = 1;
		}

		if (isset($this->request->post['shipping_shiptor_weight'])) {
			$data['shipping_shiptor_weight'] = $this->request->post['shipping_shiptor_weight'];
		} elseif ($this->config->get('shipping_shiptor_weight')) { 
			$data['shipping_shiptor_weight'] = $this->config->get('shipping_shiptor_weight');
		} else {
			$data['shipping_shiptor_weight'] = 0;
		}

		if (isset($this->request->post['shipping_shiptor_default_length'])) {
			$data['shipping_shiptor_default_length'] = $this->request->post['shipping_shiptor_default_length'];
		} elseif ($this->config->get('shipping_shiptor_default_length')) { 
			$data['shipping_shiptor_default_length'] = $this->config->get('shipping_shiptor_default_length');
		} else {
			$data['shipping_shiptor_default_length'] = '';
		}

		if (isset($this->request->post['shipping_shiptor_default_width'])) {
			$data['shipping_shiptor_default_width'] = $this->request->post['shipping_shiptor_default_width'];
		} elseif ($this->config->get('shipping_shiptor_default_width')) { 
			$data['shipping_shiptor_default_width'] = $this->config->get('shipping_shiptor_default_width');
		} else {
			$data['shipping_shiptor_default_width'] = '';
		}

		if (isset($this->request->post['shipping_shiptor_default_height'])) {
			$data['shipping_shiptor_default_height'] = $this->request->post['shipping_shiptor_default_height'];
		} elseif ($this->config->get('shipping_shiptor_default_height')) { 
			$data['shipping_shiptor_default_height'] = $this->config->get('shipping_shiptor_default_height');
		} else {
			$data['shipping_shiptor_default_height'] = '';
		}
		
		$data['shipping_shiptor_weight_class_id'] = $this->model_localisation_weight_class->getWeightClasses();
    	
		if (isset($this->request->post['shipping_shiptor_weight_class_id'])) {
			$data['shipping_shiptor_weight_class_id'] = (int)$this->request->post['shipping_shiptor_weight_class_id'];
		} else { 
			$data['shipping_shiptor_weight_class_id'] = (int)$this->config->get('shipping_shiptor_weight_class_id');
		}

		if (isset($this->request->post['shipping_shiptor_days'])) {
			$data['shipping_shiptor_days'] = $this->request->post['shipping_shiptor_days'];
		} else { 
			$data['shipping_shiptor_days'] = $this->config->get('shipping_shiptor_days');
		}
		
		if (isset($this->request->post['shipping_shiptor_increase_days'])) {
			$data['shipping_shiptor_increase_days'] = (int)$this->request->post['shipping_shiptor_increase_days']<0?0:abs($this->request->post['shipping_shiptor_increase_days']);
		} elseif ($this->config->get('shipping_shiptor_increase_days')) {
			$data['shipping_shiptor_increase_days'] = (int)$this->config->get('shipping_shiptor_increase_days')<0?0:abs($this->config->get('shipping_shiptor_increase_days'));
		} else {
			$data['shipping_shiptor_increase_days'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_time'])) {
			$data['shipping_shiptor_time'] = $this->request->post['shipping_shiptor_time'];
		} else { 
			$data['shipping_shiptor_time'] = $this->config->get('shipping_shiptor_time');
		}
		
		if (isset($this->request->post['shipping_shiptor_validate_period'])) {
			$data['shipping_shiptor_validate_period'] = (int)$this->request->post['shipping_shiptor_validate_period'];
		} elseif ($this->config->get('shipping_shiptor_validate_period')) { 
			$data['shipping_shiptor_validate_period'] = (int)$this->config->get('shipping_shiptor_validate_period');
		} else {
			$data['shipping_shiptor_validate_period'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_for_api'])) {
			$data['shipping_shiptor_order_status_for_api'] = (int)$this->request->post['shipping_shiptor_order_status_for_api'];
		} elseif ($this->config->get('shipping_shiptor_order_status_for_api')) { 
			$data['shipping_shiptor_order_status_for_api'] = (int)$this->config->get('shipping_shiptor_order_status_for_api');
		} elseif ($this->config->get('config_order_status_id')) { 
			$data['shipping_shiptor_order_status_for_api'] = (int)$this->config->get('config_order_status_id');
		}else {
			$data['shipping_shiptor_order_status_for_api'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_new'])) {
			$data['shipping_shiptor_order_status_after_api_new'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_new'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_new')) { 
			$data['shipping_shiptor_order_status_after_api_new'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_new');
		}else {
			$data['shipping_shiptor_order_status_after_api_new'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_waiting_pickup'])) {
			$data['shipping_shiptor_order_status_after_api_waiting_pickup'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_waiting_pickup'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup')) { 
			$data['shipping_shiptor_order_status_after_api_waiting_pickup'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup');
		}else {
			$data['shipping_shiptor_order_status_after_api_waiting_pickup'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_waiting_send'])) {
			$data['shipping_shiptor_order_status_after_api_waiting_send'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_waiting_send'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_waiting_send')) { 
			$data['shipping_shiptor_order_status_after_api_waiting_send'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_waiting_send');
		}else {
			$data['shipping_shiptor_order_status_after_api_waiting_send'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_arrived_to_warehouse'])) {
			$data['shipping_shiptor_order_status_after_api_arrived_to_warehouse'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_arrived_to_warehouse'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse')) { 
			$data['shipping_shiptor_order_status_after_api_arrived_to_warehouse'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse');
		}else {
			$data['shipping_shiptor_order_status_after_api_arrived_to_warehouse'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_packed'])) {
			$data['shipping_shiptor_order_status_after_api_packed'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_packed'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_packed')) { 
			$data['shipping_shiptor_order_status_after_api_packed'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_packed');
		}else {
			$data['shipping_shiptor_order_status_after_api_packed'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_prepared_to_send'])) {
			$data['shipping_shiptor_order_status_after_api_prepared_to_send'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_prepared_to_send'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send')) { 
			$data['shipping_shiptor_order_status_after_api_prepared_to_send'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send');
		}else {
			$data['shipping_shiptor_order_status_after_api_prepared_to_send'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_sent'])) {
			$data['shipping_shiptor_order_status_after_api_sent'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_sent'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_sent')) { 
			$data['shipping_shiptor_order_status_after_api_sent'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_sent');
		}else {
			$data['shipping_shiptor_order_status_after_api_sent'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_delivered'])) {
			$data['shipping_shiptor_order_status_after_api_delivered'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_delivered'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_delivered')) { 
			$data['shipping_shiptor_order_status_after_api_delivered'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_delivered');
		}else {
			$data['shipping_shiptor_order_status_after_api_delivered'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_waiting_on_delivery_point'])) {
			$data['shipping_shiptor_order_status_after_api_waiting_on_delivery_point'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_waiting_on_delivery_point'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point')) { 
			$data['shipping_shiptor_order_status_after_api_waiting_on_delivery_point'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point');
		}else {
			$data['shipping_shiptor_order_status_after_api_waiting_on_delivery_point'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_removed'])) {
			$data['shipping_shiptor_order_status_after_api_removed'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_removed'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_removed')) { 
			$data['shipping_shiptor_order_status_after_api_removed'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_removed');
		}else {
			$data['shipping_shiptor_order_status_after_api_removed'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_returned'])) {
			$data['shipping_shiptor_order_status_after_api_returned'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_returned'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_returned')) { 
			$data['shipping_shiptor_order_status_after_api_returned'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_returned');
		}else {
			$data['shipping_shiptor_order_status_after_api_returned'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_reported'])) {
			$data['shipping_shiptor_order_status_after_api_reported'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_reported'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_reported')) { 
			$data['shipping_shiptor_order_status_after_api_reported'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_reported');
		}else {
			$data['shipping_shiptor_order_status_after_api_reported'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_lost'])) {
			$data['shipping_shiptor_order_status_after_api_lost'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_lost'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_lost')) { 
			$data['shipping_shiptor_order_status_after_api_lost'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_lost');
		}else {
			$data['shipping_shiptor_order_status_after_api_lost'] = 0;
		}
		
		if (isset($this->request->post['shipping_shiptor_order_status_after_api_resend'])) {
			$data['shipping_shiptor_order_status_after_api_resend'] = (int)$this->request->post['shipping_shiptor_order_status_after_api_resend'];
		} elseif ($this->config->get('shipping_shiptor_order_status_after_api_resend')) { 
			$data['shipping_shiptor_order_status_after_api_resend'] = (int)$this->config->get('shipping_shiptor_order_status_after_api_resend');
		}else {
			$data['shipping_shiptor_order_status_after_api_resend'] = 0;
		}

		if (isset($this->request->post['shipping_shiptor_regions'])) {
			$regions = $this->request->post['shipping_shiptor_regions'];
		} elseif ($this->config->get('shipping_shiptor_regions')) {
			$regions = $this->config->get('shipping_shiptor_regions');
		} else {
			$regions = array();
		}
		
		$data['regions'] = array();
		
		if ($regions) {
			foreach ($regions as $id) {
				if ($region_info = $this->model_extension_shipping_shiptor->getRegionId($id)) {
					$data['regions'][] = array('id' => $region_info['id'], 'name' => $region_info['name']);
				}
			}
		}
		
		if($this->config->get('config_country_id')){
			$this->load->model('localisation/country');

			$result = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
			$data['country'] = $result['iso_code_2'];
		}else{
			$data['country'] = 'RU';
		}

		$data['shipping_methods'] = array();

		# Shipping settings
		if ($shipping_methods) {
			foreach ($shipping_methods as $shipping_method) {
				if($shipping_method['courier']=='aramex')continue;
				
				if(stristr($shipping_method['category'],'-to-')){
					$data['shipping_methods'][0][] = $shipping_method;
				}else{
					$data['shipping_methods'][1][] = $shipping_method;
				}
			}
			
			foreach ($shipping_methods as $shipping_method) {
				if ($shipping_method['category'] == 'delivery-point-to-door' 
				|| $shipping_method['category'] == 'delivery-point-to-delivery-point') {
					# Получения списка ПВЗ
					$params['weight'] = $this->config->get('shipping_shiptor_weight');
					$params['self_pick_up'] = true;
					$params['kladr_id'] = $this->config->get('shipping_shiptor_city_delivery_value');
					$params['shipping_method'] = $shipping_method['id'];
					$data['array_pvz_' .$shipping_method['id']] = $this->shiptor->getDeliveryPoints($params);
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_status'])) {
					$data['shipping_status_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_status'];
				} else { 
					$data['shipping_status_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_status');
				}
								
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_weight_price'])) {
					$data['shipping_weight_price_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_weight_price'];
				} else {
					$data['shipping_weight_price_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_weight_price');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_free'])) {
					$data['shipping_total_free_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_free'];
				} else {
					$data['shipping_total_free_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_total_free');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_name'])) {
					$data['shipping_name_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_name'];
				} else {
					$data['shipping_name_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_name');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_min'])) {
					$data['shipping_total_min_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_min'];
				} else {
					$data['shipping_total_min_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_total_min');
				}				
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_max'])) {
					$data['shipping_total_max_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_max'];
				} else {
					$data['shipping_total_max_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_total_max');
				}				
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_min_weight'])) {
					$data['shipping_total_min_weight_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_min_weight'];
				} else {
					$data['shipping_total_min_weight_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_total_min_weight');
				}				
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_max_weight'])) {
					$data['shipping_total_max_weight_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_total_max_weight'];
				} else {
					$data['shipping_total_max_weight_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_total_max_weight');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_fixed'])) {
					$data['shipping_fixed_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_fixed'];
				} else {
					$data['shipping_fixed_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_fixed');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_type'])) {
					$data['shipping_type_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_type'];
				} else {
					$data['shipping_type_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_type');
				}
				
				if ($shipping_method['category'] == 'delivery-point-to-door' && $shipping_method['courier'] == 'dpd') { 
					$shiptor_dublicate = $this->model_extension_shipping_shiptor->getPvz('delivery-point-to-delivery-poi',$shipping_method['courier']);
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'])) {
						$data['shipping_pvz_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'];
						$data['shipping_pvz_' . $shiptor_dublicate['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz');
					} elseif($this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz')) {
						$data['shipping_pvz_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz');
						$data['shipping_pvz_' . $shiptor_dublicate['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz');
					}
				}elseif($shipping_method['category'] == 'delivery-point-to-door' && $shipping_method['courier'] == 'cdek') { 
					$shiptor_dublicate = $this->model_extension_shipping_shiptor->getPvz('delivery-point-to-delivery-poi',$shipping_method['courier']);
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'])) {
						$data['shipping_pvz_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'];
						$data['shipping_pvz_' . $shiptor_dublicate['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'];
					} elseif($this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz')) {
						$data['shipping_pvz_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz');
						$data['shipping_pvz_' . $shiptor_dublicate['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz');
					}
				}elseif($shipping_method['category'] == 'delivery-point-to-door' && $shipping_method['courier'] == 'shiptor') { 
					$shiptor_dublicate = $this->model_extension_shipping_shiptor->getPvz('delivery-point-to-delivery-poi',$shipping_method['courier']);
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'])) {
						$data['shipping_pvz_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'];
						$data['shipping_pvz_' . $shiptor_dublicate['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_pvz'];
					} elseif($this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz')) {
						$data['shipping_pvz_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz');
						$data['shipping_pvz_' . $shiptor_dublicate['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_pvz');
					}
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_markup'])) {
					$data['shipping_markup_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_markup'];
				} else {
					$data['shipping_markup_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_markup');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_markup_surcharge'])) {
					$data['shipping_markup_surcharge_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_markup_surcharge'];
				} else {
					$data['shipping_markup_surcharge_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_markup_surcharge');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_sort_order'])) {
					$data['shipping_sort_order_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_sort_order'];
				} else { 
					$data['shipping_sort_order_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_sort_order');
				}

				# Shiptor 1-day Курьер
				if ($shipping_method['courier'] == 'shiptor-one-day') {
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_time'])) {
						$data['shipping_time_' . $shipping_method['id']] = preg_replace('/[a-zAZ\.*+? ]/' , '', $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_time']);
					} else { 
						$data['shipping_time_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_time');
					}
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_comment'])) {
					$data['shipping_comment_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_comment'];
				} else { 
					$data['shipping_comment_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_comment');
				}
				
				if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_my_comment'])) {
					$data['shipping_my_comment_' . $shipping_method['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_my_comment'];
				} else {
					$data['shipping_my_comment_' . $shipping_method['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_my_comment');
				}

				# Geo zone settings
				foreach ($data['regions'] as $region) {			
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_weight_price'])) {
						$data['shipping_weight_price_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_weight_price'];
					} else {
						$data['shipping_weight_price_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_weight_price');
					}
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_free'])) {
						$data['shipping_total_free_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_free'];
					} else {
						$data['shipping_total_free_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_free');
					}
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_min'])) {
						$data['shipping_total_min_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_min'];
					} else {
						$data['shipping_total_min_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_min');
					}					
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_max'])) {
						$data['shipping_total_max_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_max'];
					} else {
						$data['shipping_total_max_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_max');
					}
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_min_weight'])) {
						$data['shipping_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_min_weight'];
					} else {
						$data['shipping_total_min_weight_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_min_weight');
					}					
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_max_weight'])) {
						$data['shipping_total_max_weight_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_max_weight'];
					} else {
						$data['shipping_total_max_weight_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_total_max_weight');
					}
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_fixed'])) {
						$data['shipping_fixed_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_fixed'];
					} else {
						$data['shipping_fixed_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_fixed');
					}

					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_markup'])) {
						$data['shipping_markup_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_markup'];
					} else {
						$data['shipping_markup_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_markup');
					}
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_type'])) {
						$data['shipping_type_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_type'];
					} else {
						$data['shipping_type_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_type');
					}
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_status'])) {
						$data['shipping_status_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_status'];
					} else {
						$data['shipping_status_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_status');
					}
					
					if (isset($this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_hide'])) {
						$data['shipping_hide_' . $shipping_method['id'] . '_' . $region['id']] = $this->request->post['shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_hide'];
					} else {
						$data['shipping_hide_' . $shipping_method['id'] . '_' . $region['id']] = $this->config->get('shipping_shiptor_' . $shipping_method['id'] . '_' . $region['id'] . '_hide');
					}
				}
			}
		}
		
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['header'] 	 = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] 	 = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/shiptor', $data));
	}
	
	public function exportProduct(){
		$this->load->model('extension/shipping/shiptor');
		$this->load->model('catalog/product');
		
		$json = array();
		// Получение списка товаров
		$product_infos = $this->model_catalog_product->getProducts();
		if(!empty($product_infos)){
			foreach($product_infos as $product_info){
			// Проверка артикла и модели по приоритету
				if(($this->config->get('shipping_shiptor_id_product')) && (!empty($product_info['sku']))){
					$article = $product_info['sku'];
				}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product_info['model']))){
					$article = $product_info['model'];
				}elseif(!empty($product_info['sku'])){
					$article = $product_info['sku'];
				}else{
					$article = $product_info['model'];
				}
				$name = $product_info['name'];
				// Получения списка основных опций
				$main_product_options = $this->model_extension_shipping_shiptor->getProductMainOption($product_info['product_id']);
				if(!empty($main_product_options)){
					
					// Подсчёт основных опций
					$count_option_id = count($main_product_options);
					$array = array();
					/* Преобразование в полноценный массив. Разбитие на 2 массива с заглавными ключами основных опций */
					foreach($main_product_options as $key => $option){
						$product_options = $this->model_extension_shipping_shiptor->getProductOptions($product_info['product_id'], $option['option_id']);
						$array[$option['option_id']] = $product_options;
					}
					if(isset($values)){
						unset($values);
					}
					$k = 0;
					foreach($array as $key => $option){
						$j = 0;
						$count_options = count($option);
						$values[$k]['name'] = '';
						$values[$k]['price'] = '';
						$values[$k]['weight'] = '';
						
						foreach($option as $option_value){
							if($k > $count_options){
							
							}else{
								if(++$j == $count_options){
									$values[$k]['name'] .= $option_value['name'];
									$values[$k]['price'] .= $option_value['price'];
									$values[$k]['weight'] .= $option_value['weight'];
								}else{
									$values[$k]['name'] .= $option_value['name'] . '.';
									$values[$k]['price'] .= $option_value['price'] . ',';
									$values[$k]['weight'] .= $option_value['weight'] . ';';
								}
							}
						}
						$k++;
					}
					
					if(isset($pieces)){
						unset($pieces);
					}
					
					$reload_to_two_array = array_chunk($values, 1, TRUE);
					foreach($reload_to_two_array as $key => $value){
						foreach($value as $j => $val_opt){
							$pieces[$j]['name'] = explode('.', $val_opt['name']);
							$pieces[$j]['price'] = explode(',', $val_opt['price']);
							$pieces[$j]['weight'] = explode(';', $val_opt['weight']);
						}
					}
					/* Преобразование в полноценный массив. Разбитие на 2 массива с заглавными ключами основных опций */
					
					// Отправляем функцию getShopArticle Для получения списка вариантов товаров
					
					$product_shiptor = $this->getAddProduct($product_info['product_id'], $product_info['name'], $count_option_id, $article, $pieces);
					
					if(isset($product_shiptor['error'])){
						$json['error'] = $product_shiptor['error'];
					}else{
						foreach($product_shiptor as $key_product => $shiptor){
							foreach($shiptor as $product){
								$price = $product_info['price'] + $product['price'];
								$weight = $product_info['weight'] + $product['weight'];
								$product_shiptor_params[] = array(
									'name'          => $product['product_name'],
									'article'       => empty($product_info['sku']) ? '' : $product_info['sku'],
									'shopArticle'   => $product['name'],
									'length'		=> empty($product_info['length']) ? '' : $this->length->convert($product_info['length'], 1, 1),
									'width'         => empty($product_info['width']) ? '' : $this->length->convert($product_info['width'],  1, 1),
									'height'        => empty($product_info['height']) ? '' : $this->length->convert($product_info['height'], 1, 1),
									'weight'        => empty($weight) ? '' : number_format($weight, 8, '.', ' '),
									'price'         => round($price, $this->currency->getDecimalPlace($this->config->get('config_currency'))),
								);
							}
						}
					}
					
				}else{
					$product_shiptor_params[] = array(
						'name'          => $product_info['name'],
						'article'       => empty($product_info['sku']) ? '' : $product_info['sku'],
						'shopArticle'   => $article,
						'length'		=> empty($product_info['length']) ? '' : $this->length->convert($product_info['length'], 1, 1),
						'width'         => empty($product_info['width']) ? '' : $this->length->convert($product_info['width'],  1, 1),
						'height'        => empty($product_info['height']) ? '' : $this->length->convert($product_info['height'], 1, 1),
						'weight'        => empty($product_info['weight']) ? '' : $this->length->convert($product_info['weight'], 1, 1),
						'price'         => round($product_info['price'], $this->currency->getDecimalPlace($this->config->get('config_currency'))),
					);
				}
			}
			
			// Убираем повторяющиеся элементы
			$product_shiptor_params = array_map("unserialize", array_unique(array_map("serialize", $product_shiptor_params)));
			
			// Добавляем/Редактируем номенклатуру
			foreach($product_shiptor_params as $product_info){
				$product_shiptor_param = array(
					'name'          => $product_info['name'],
					'article'       => $product_info['article'],
					'shopArticle'   => $product_info['shopArticle'],
					'length'		=> $product_info['length'],
					'width'         => $product_info['width'],
					'height'        => $product_info['height'],
					'weight'        => $this->length->convert($product_info['weight'], 1, 1),
					'price'         => round($product_info['price'], $this->currency->getDecimalPlace($this->config->get('config_currency'))),
				);
				$get_product = $this->shiptor->getProducts($product_shiptor_param);
				if(empty($get_product)){
					$this->shiptor->addProduct($product_shiptor_param);
				}else{
					$this->shiptor->editProduct($product_shiptor_param);
				}
			}
			$json['success'] = 'Выгрузка успешно завершена!';
		}else{
			$json['error'] = 'В Вашем магине нет товаров!';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	private function getAddProduct($product_id, $product_name, $count_option_id, $article, $pieces, $main_count = 0){
		foreach($pieces[0] as $key1 => $option1){
			if(1 !== $count_option_id){
				foreach($pieces[1] as $key2 => $option2){
					if(2 !== $count_option_id){
						foreach($pieces[2] as $key3 => $option3){
							if(3 !== $count_option_id){
								foreach($pieces[3] as $key4 => $option4){
									if(4 !== $count_option_id){
										foreach($pieces[4] as $key5 => $option5){
											if(5 !== $count_option_id){
												$shopArticle['error'] = 'Привышено кол-во опций в товаре: <a href="' . $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . 'product_id=' . $product_id, 'SSL') . '"><b>' . $product_name . '</b></a>';
											}else{
												foreach($option1 as $keyOne => $optionOne){
													foreach($option2 as $keyTwo => $optionTwo){
														foreach($option3 as $keyThree => $optionThree){
															foreach($option4 as $keyFour => $optionFour){
																foreach($option5 as $keyFive => $optionFive){
																	$shopArticle[$product_id][] = array(
																		'name' 			=> $article . '-' .  $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo] . '-' . $pieces[2]['name'][$keyThree] . '-' . $pieces[3]['name'][$keyFour] . '-' . $pieces[4]['name'][$keyFive],
																		'price'			=> $pieces[0]['price'][$keyOne] + $pieces[1]['price'][$keyTwo] + $pieces[2]['price'][$keyThree] + $pieces[3]['price'][$keyFour] + $pieces[4]['price'][$keyFive],
																		'weight'		=> $pieces[0]['weight'][$keyOne] + $pieces[1]['weight'][$keyTwo] + $pieces[2]['weight'][$keyThree] + $pieces[3]['weight'][$keyFour] + $pieces[4]['weight'][$keyFive],
																		'product_name'	=> $product_name . '-' . $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo] . '-' . $pieces[2]['name'][$keyThree] . '-' . $pieces[3]['name'][$keyFour] . '-' . $pieces[4]['name'][$keyFive],
																	);
																}
															}
														}
													}
												}
											}
										}
									}else{
										foreach($option1 as $keyOne => $optionOne){
											foreach($option2 as $keyTwo => $optionTwo){
												foreach($option3 as $keyThree => $optionThree){
													foreach($option4 as $keyFour => $optionFour){
														$shopArticle[$product_id][] = array(
															'name' 			=> $article . '-' .  $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo] . '-' . $pieces[2]['name'][$keyThree] . '-' . $pieces[3]['name'][$keyFour],
															'price'			=> $pieces[0]['price'][$keyOne] + $pieces[1]['price'][$keyTwo] + $pieces[2]['price'][$keyThree] + $pieces[3]['price'][$keyFour],
															'weight'		=> $pieces[0]['weight'][$keyOne] + $pieces[1]['weight'][$keyTwo] + $pieces[2]['weight'][$keyThree] + $pieces[3]['weight'][$keyFour],
															'product_name'	=> $product_name . '-' . $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo] . '-' . $pieces[2]['name'][$keyThree] . '-' . $pieces[3]['name'][$keyFour],
														);
													}
												}
											}
										}
									}
								}
							}else{
								foreach($option1 as $keyOne => $optionOne){
									foreach($option2 as $keyTwo => $optionTwo){
										foreach($option3 as $keyThree => $optionThree){
											$shopArticle[$product_id][] = array(
												'name' 			=> $article . '-' . $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo] . '-' . $pieces[2]['name'][$keyThree],
												'price'			=> $pieces[0]['price'][$keyOne] + $pieces[1]['price'][$keyTwo] + $pieces[2]['price'][$keyThree],
												'weight'		=> $pieces[0]['weight'][$keyOne] + $pieces[1]['weight'][$keyTwo] + $pieces[2]['weight'][$keyThree],
												'product_name'	=> $product_name . '-' . $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo] . '-' . $pieces[2]['name'][$keyThree],
											);
										}
									}
								}
							}
						}
					}else{
						foreach($option1 as $keyOne => $optionOne){
							foreach($option2 as $keyTwo => $optionTwo){
								$shopArticle[$product_id][] = array(
									'name' 			=> $article . '-' .  $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo],
									'price'			=> $pieces[0]['price'][$keyOne] + $pieces[1]['price'][$keyTwo],
									'weight'		=> $pieces[0]['weight'][$keyOne] + $pieces[1]['weight'][$keyTwo],
									'product_name'	=> $product_name . '-' . $pieces[0]['name'][$keyOne] . '-' . $pieces[1]['name'][$keyTwo],
								);
							}
						}
					}
				}
			}else{
				foreach($option1 as $keyOne => $optionOne){
					$shopArticle[$product_id][] = array(
						'name' 			=> $article . '-' .  $pieces[0]['name'][$keyOne],
						'price'			=> $pieces[0]['price'][$keyOne],
						'weight'		=> $pieces[0]['weight'][$keyOne],
						'product_name'	=> $product_name . '-' . $pieces[0]['name'][$keyOne],
					);
				}
			}
		}
		
		return $shopArticle;
	}

	# Список заказов Shiptor
	public function shiptor() {
		$this->load->language('extension/shipping/shiptor');
		
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_order_shiptor'), 'href' => $this->url->link('extension/shipping/shiptor/shiptor', 'user_token=' . $this->session->data['user_token'], true));
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['module']   = $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true);
		$data['customer'] = $this->url->link('extension/shipping/shiptor/customer', 'user_token=' . $this->session->data['user_token'], true);
		$data['order']    = $this->url->link('extension/shipping/shiptor/order', 'user_token=' . $this->session->data['user_token'], true);
		

		$data['heading_title']		= $this->language->get('text_order_shiptor');

		$data['text_faq']		= $this->language->get('text_faq');
		$data['text_module_setting']	= $this->language->get('text_module_setting');
		$data['text_order_customer']	= $this->language->get('text_order_customer');
		$data['text_order_store']	= $this->language->get('text_order_store');
		
		$data['column_shiptor_id']	= $this->language->get('column_shiptor_id');
		$data['column_shipment_id']	= $this->language->get('column_shipment_id');
		$data['column_date_shipment']	= $this->language->get('column_date_shipment');
		$data['column_status']		= $this->language->get('column_status');
		$data['column_order_id']	= $this->language->get('column_order_id');
		$data['column_label']		= $this->language->get('column_label');
		$data['column_customer']	= $this->language->get('column_customer');
		$data['column_phone']		= $this->language->get('column_phone');
		$data['column_email']		= $this->language->get('column_email');
		$data['column_address']		= $this->language->get('column_address');
		$data['column_tracking']	= $this->language->get('column_tracking');
		$data['column_weight']		= $this->language->get('column_weight');
		$data['column_cod']		= $this->language->get('column_cod');
		$data['column_delivery']	= $this->language->get('column_delivery');
		$data['column_time']		= $this->language->get('column_time');
		$data['column_limits']		= $this->language->get('column_limits');
		$data['column_history']		= $this->language->get('column_history');
		$data['column_checkpoints']	= $this->language->get('column_checkpoints');
		$data['column_problems']	= $this->language->get('column_problems');
		$data['column_confirmed']	= $this->language->get('column_confirmed');
		$data['column_external_tracking_number']	= $this->language->get('column_external_tracking_number');

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/shiptor/shiptor', $data));
	}
		
	public function shiptor_table() {
		$json = array();
		
		if ($this->user->hasPermission('modify', 'extension/shipping/shiptor')) {
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 0;
			}		
			
			if (isset($this->request->get['per_page'])) {
				$per_page = $this->request->get['per_page'];
			} else {
				$per_page = 50;
			}
			
			if (isset($this->request->get['filter'])) {
				$filter = $this->request->get['filter'];
			} else {
				$filter = null;
			}

			if ($filter == 'delivered') {
				$params['per_page'] = true;
			}

			if ($filter == 'returned') {
				$params['returned'] = true;
			}		
			
			$params['per_page'] = $per_page;
			$params['page']		= $page / $per_page + 1;

			$orders = $this->shiptor->getPackages($params);

			$total = $this->shiptor->getPackagesCount($params);

			$json['total'] = 0;
			
			if (isset($total['count'])) {
				$json['total'] = $total['count'];
			}

			$json['rows'] = array();

			if ($orders) {
				foreach ($orders as $order) {					
					$customer = '';
			
					if (! empty($order['departure']['address']['name'])) {
						$customer .= $order['departure']['address']['name'];
					}
					
					if (! empty($order['departure']['address']['surname'])) {
						$customer .= ' ' . $order['departure']['address']['surname'];
					}
					
					if (! empty($order['departure']['address']['patronymic'])) {
						$customer .= ' ' . $order['departure']['address']['patronymic'];
					}
					
					$address = '';
					
					if (! empty($order['departure']['address']['postal_code'])) {
						$address .= ' ' . $order['departure']['address']['postal_code'];
					}
					
					if (! empty($order['departure']['address']['administrative_area'])) {
						$address .= ' ' . $order['departure']['address']['administrative_area'];
					}
					
					if (! empty($order['departure']['address']['settlement'])) {
						$address .= ' ' . $order['departure']['address']['settlement'];
					}
					
					if (! empty($order['departure']['address']['street'])) {
						$address .= ' ' . $order['departure']['address']['street'];
					}
					
					if (! empty($order['departure']['address']['house'])) {
						$address .= ' ' . $order['departure']['address']['house'];
					}			
					
					if (! empty($order['departure']['address']['apartment'])) {
						$address .= ' ' . $order['departure']['address']['apartment'];
					}
					
					if (! empty($order['shipment']['id'])) {
						$shipment_id = $order['shipment']['id'];
					}else{
						$shipment_id = '-';
					}
					
					if (! empty($order['shipment']['pickup_date'])) {
						$date = $order['shipment']['pickup_date'];
					}else{
						$date = '-';
					}
					
					if (! empty($order['external_tracking_number'])) {
						$external_tracking_number = $order['external_tracking_number'];
					}else{
						$external_tracking_number = '';
					}
					
					if (!empty($order['shipment'])) {
						if (! empty($order['shipment']['confirmed'])) {
							$confirmed = '<i style="color:green; border-radius: 50px 50px 50px 50px; padding: 5px; border: 1px solid green;" class="fa fa-check icon-check"></i></span>';
						}else{
							$confirmed = '<i style="color:red; font-size:20px;" class="fa fa-remove icon-remove"></i></span>';
						}
					}else{
						$confirmed = '';
					}
					
					$history = array();
					
					if (isset($order['history'])) {
						foreach ($order['history'] as $value) {
							$history[] = array('date' => date('d-m-Y H:s:i', strtotime($value['date'])), 'event' => $value['event']);
						}
					}	

					$checkpoints = array();
					
					if (isset($order['checkpoints'])) {
						foreach ($order['checkpoints'] as $value) {
							$checkpoints[] = array('date' => date('d-m-Y H:s:i', strtotime($value['date'])), 'message' => $value['message']);
						}
					}
					
					$problems = array();
					
					if (isset($order['problems'])) {
						foreach ($order['problems'] as $value) {
							$problems[] = array('created_at' => date('d-m-Y H:s:i', strtotime($value['created_at'])), 'name' => $value['name']);
						}
					}

					$json['rows'][] = array(
						'shiptor_id' 	  => '<a href="https://shiptor.ru/account/package/view/' . $order['id'] . '" target="_blank">' . $order['id'] . '</a>',
						'shipment_id' 	  => ($shipment_id !== '-') ? '<a href="https://shiptor.ru/account/shipment/view/' . $shipment_id . '" target="_blank">' . $shipment_id . '</a>' : $shipment_id,
						'date' 	  		  => $date,
						'status' 	      => $order['status'],
						'label_url'   	  => $order['label_url'],
						'order'   	      => array('order_id' => $order['external_id'], 'href' => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order['external_id'], true)),
						'tracking'     	  => $order['tracking_number'],
						'weight'    	  => $order['weight'],
						'cod'     		  => $order['cod'],
						'shipping_method' => $order['departure']['shipping_method']['name'],
						'delivery_time'   => $order['delivery_time'],
						'name'   		  => $customer,
						'email'   		  => $order['departure']['address']['email'],
						'phone'   		  => $order['departure']['address']['phone'],
						'address'   	  => $address,
						'limits'   	  	  => $order['departure']['delivery_point']['limits'],
						'history'   	  => $history,
						'checkpoints'  	  => $checkpoints,
						'problems'  	  => $problems,
						'confirmed'		  => $confirmed,
						'external_t_n'	  => $external_tracking_number,
					);
				}
			}
		}	
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
	
	public function customer() {
		$this->load->language('extension/shipping/shiptor');

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_order_customer'), 'href' => $this->url->link('extension/shipping/shiptor/customer', 'user_token=' . $this->session->data['user_token'], true));

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['module']  = $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true);
		$data['order']   = $this->url->link('extension/shipping/shiptor/order', 'user_token=' . $this->session->data['user_token'], true);
		$data['shiptor'] = $this->url->link('extension/shipping/shiptor/shiptor', 'user_token=' . $this->session->data['user_token'], true);

		$data['heading_title']		= $this->language->get('text_order_customer');

		$data['text_module_setting']	= $this->language->get('text_module_setting');
		$data['text_order_store']	= $this->language->get('text_order_store');
		$data['text_order_shiptor']	= $this->language->get('text_order_shiptor');
		
		$data['error_is_numeric']	= $this->language->get('error_is_numeric');
		
		$data['button_filter']		= $this->language->get('button_filter');
		
		$data['column_recipient']	= $this->language->get('column_recipient');
		$data['column_customer']	= $this->language->get('column_customer');
		$data['column_address']		= $this->language->get('column_address');
		$data['column_kladr_id']	= $this->language->get('column_kladr_id');
		
		$data['text_filter_customer']	= $this->language->get('text_filter_customer');
		$data['text_filter_email']	= $this->language->get('text_filter_email');
		$data['text_filter_telephone']	= $this->language->get('text_filter_telephone');
		$data['text_filter_kladr_id']	= $this->language->get('text_filter_kladr_id');

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/shiptor/customer', $data));
	}
	
	public function customer_table() {
		$json = array();
		
		$this->load->language('extension/shipping/shiptor');

		if ($this->user->hasPermission('modify', 'extension/shipping/shiptor')) {
			$this->load->model('extension/shipping/shiptor');

			if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
				if (isset($this->request->post['type'], $this->request->post['address_id'])) {					
					if ($this->request->post['type'] == 'remove') {
						$this->model_extension_shipping_shiptor->removeCustomer($this->request->post['address_id']);
						
						$json['success'] = $this->language->get('success_remove');
					}

					if ($this->request->post['type'] == 'save') {				
						if (isset($this->request->post['kladr_id'])) {
							$this->model_extension_shipping_shiptor->editCustomer($this->request->post['address_id'], $this->request->post['customer_id'], $this->request->post['kladr_id']);
							
							$json['success'] = $this->language->get('success_update');
						}
					}
					
					$this->response->addHeader('Content-Type: application/json');
					return $this->response->setOutput(json_encode($json));
				}
			}

			if (isset($this->request->get['filter_customer'])) {
				$filter_customer = $this->request->get['filter_customer'];
			} else {
				$filter_customer = null;
			}
			
			if (isset($this->request->get['filter_email'])) {
				$filter_email = $this->request->get['filter_email'];
			} else {
				$filter_email = null;
			}			
			
			if (isset($this->request->get['filter_telephone'])) {
				$filter_telephone = $this->request->get['filter_telephone'];
			} else {
				$filter_telephone = null;
			}			
			
			if (isset($this->request->get['filter_kladr_id'])) {
				$filter_kladr_id = $this->request->get['filter_kladr_id'];
			} else {
				$filter_kladr_id = null;
			}		

			if (isset($this->request->get['per_page'])) {
				$per_page = $this->request->get['per_page'];
			} else {
				$per_page = 50;
			}
			
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			$filter_data = array(
				'filter_customer'  => $filter_customer,
				'filter_email'     => $filter_email,
				'filter_telephone' =>preg_replace("/(\D)/", "", urldecode($filter_telephone)),
				'filter_kladr_id'  => $filter_kladr_id,			
				'start'            => ($page / $per_page) * $per_page,
				'limit'            => $per_page
			);			
				
			$json['total'] = $this->model_extension_shipping_shiptor->getTotalCustomer($filter_data);

			$json['rows'] = array();
			
			$customers = $this->model_extension_shipping_shiptor->getCustomer($filter_data);
		
			if ($customers) {
				foreach ($customers as $customer) {
					$json['rows'][] = array(
						'address_id'  => $customer['address_id'],
						'recipient'   => $customer['recipient'],
						'customer_id' => $customer['customer_id'],
						'customer'    => $customer['customer'],			
						'address'     => $customer['address'],			
						'kladr_id'    => $customer['kladr_id']
					);
				}
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}	
	
	public function order() {
		$this->load->language('extension/shipping/shiptor');

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true));
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_order_store'), 'href' => $this->url->link('extension/shipping/shiptor/order', 'user_token=' . $this->session->data['user_token'], true));

		$data['module']   = $this->url->link('extension/shipping/shiptor', 'user_token=' . $this->session->data['user_token'], true);
		$data['shiptor']  = $this->url->link('extension/shipping/shiptor/shiptor', 'user_token=' . $this->session->data['user_token'], true);
		$data['customer'] = $this->url->link('extension/shipping/shiptor/customer', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['user_token'] = $this->session->data['user_token'];
		
		if($this->config->get('shipping_shiptor_city_delivery')){
			$data['city_valid'] = 1;
		}else{
			$data['city_valid'] = 0;
		}

		$data['heading_title']		= $this->language->get('text_order_store');

		$data['text_faq']		= $this->language->get('text_faq');
		$data['text_module_setting']	= $this->language->get('text_module_setting');
		$data['text_order_customer']	= $this->language->get('text_order_customer');
		$data['text_order_shiptor']	= $this->language->get('text_order_shiptor');

		$data['text_not_selected']	= $this->language->get('text_not_selected');
		
		$data['error_is_numeric']	= $this->language->get('error_is_numeric');
		$data['error_is_telephone']	= $this->language->get('error_is_telephone');
		$data['error_is_email']		= $this->language->get('error_is_email');
		
		$data['button_filter']		= $this->language->get('button_filter');
		
		$data['column_order_id']	= $this->language->get('column_order_id');
		$data['column_shiptor_id']	= $this->language->get('column_shiptor_id');
		$data['column_customer']	= $this->language->get('column_customer');
		$data['column_email']		= $this->language->get('column_email');
		$data['column_telephone']	= $this->language->get('column_telephone');
		$data['column_delivery']	= $this->language->get('column_delivery');
		$data['column_time']		= $this->language->get('column_time');
		$data['column_kladr_id']	= $this->language->get('column_kladr_id');
		$data['column_point_address']	= $this->language->get('column_point_address');
		$data['column_city']		= $this->language->get('column_city');
		$data['column_street']		= $this->language->get('column_street');
		$data['column_house']		= $this->language->get('column_house');
		$data['column_apartment']	= $this->language->get('column_apartment');
		$data['column_weight']		= $this->language->get('column_weight');
		$data['column_cod']			= $this->language->get('column_cod');
		$data['column_payment']		= $this->language->get('column_payment');
		$data['column_total']		= $this->language->get('column_total');
		$data['column_date_added']	= $this->language->get('column_date_added');
		$data['column_date_shipment']	= $this->language->get('column_date_shipment');
		$data['column_shipment_id']	= $this->language->get('column_shipment_id');
		$data['column_status']		= $this->language->get('column_status');

		$data['text_filter_customer']	= $this->language->get('text_filter_customer');
		$data['text_filter_email']	= $this->language->get('text_filter_email');
		$data['text_filter_telephone']	= $this->language->get('text_filter_telephone');
		$data['text_filter_date_added']	= $this->language->get('text_filter_date_added');
		
		//page filter
		$this->load->model('extension/shipping/shiptor');
		
		if($this->config->get('shipping_shiptor_validate_period')){
			$count['orders'] = $this->model_extension_shipping_shiptor->getDateOrdersCourier($this->config->get('shipping_shiptor_validate_period'));
			$data['counts'] = count($count['orders']);
		}else{
			$count['orders'] = $this->model_extension_shipping_shiptor->getDateOrdersCourier(604800);
			$data['counts'] = count($count['orders']);
		}
		
		if($this->config->get('shipping_shiptor_postponement_delivery')){
			$date_shipment_for_array = time() + $this->config->get('shipping_shiptor_postponement_delivery');
			$date_shipment = time() + $this->config->get('shipping_shiptor_postponement_delivery');
			$dt['date_shipment'] = $date_shipment + 604800;
			$dt['date_shipment_from'] = time();
			$shiptor = $this->shiptor->getDaysOffShipment($dt);
			if(!empty($shiptor)){
				$i = 0;
				foreach($shiptor as $key => $date){
					if(date('Y-m-d', $date_shipment_for_array) == $date){
						$date_shipment_for_array += 86400;
						$i++;
					}
					$json['days'][$key] = date('d-m-Y',strtotime($date));
				}
				$time_shiptor = $i * 86400;
				$date_shipment += $time_shiptor;
			}
		}
		if($this->config->get('shipping_shiptor_postponement_delivery')){
			$data['date'] = date('d.m.Y', $date_shipment);
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = null;
		}
		
		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = null;
		}			
			
		if (isset($this->request->get['filter_telephone'])) {
			$filter_telephone = $this->request->get['filter_telephone'];
		} else {
			$filter_telephone = null;
		}			
			
		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}		

		if (isset($this->request->get['per_page'])) {
			$per_page = $this->request->get['per_page'];
		} else {
			$per_page = 10;
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
			
		if (isset($this->request->get['filter_order_status'])) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = null;
		}

		$filter_data = array(
			'filter_customer'   	=> $filter_customer,
			'filter_email'      	=> $filter_email,
			'filter_telephone'  	=> preg_replace("/(\D)/", "", urldecode($filter_telephone)),
			'filter_date_added'		=> $filter_date_added,
			'filter_order_status'	=> $filter_order_status,
			'start'             	=> ($page / $per_page) * $per_page,
			'limit'             	=> $per_page
		);

		$data['total'] = $this->model_extension_shipping_shiptor->getTotalOrders($filter_data);
		
		//page filter
		
		$data['times'] = $this->shiptor->getDeliveryTime();
		
		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');
		
		$this->load->model('localisation/order_status');
		$data['entry_all_order_status']		= $this->language->get('entry_all_order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->response->setOutput($this->load->view('extension/shipping/shiptor/order', $data));
	}	
	
	public function shiptor_status(){
		$json = array();
		$this->load->language('extension/shipping/shiptor');

		if ($this->user->hasPermission('modify', 'extension/shipping/shiptor')) { 
			$this->load->model('extension/shipping/shiptor');
			
			if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
				if (isset($this->request->post['type']) && $this->request->post['order_id'] !== '0') {
					if ($this->request->post['type'] == 'status') {
						$shiptor = $this->model_extension_shipping_shiptor->getOrder($this->request->post['order_id']);
						$shiptorPackage = $this->shiptor->getPackage($shiptor['shiptor_id']);
						
						if(!empty($shiptorPackage)){
							if($shiptorPackage['status'] == 'new'){
								$validate = false;
								foreach($shiptorPackage['history'] as $shiptor){
									if(trim($shiptor['event']) == 'Назначен бин'){
										$validate = true;
									}
								}
								if($validate == true){
									$json['status'] = 'old';
								}else{
									$json['status'] = $shiptorPackage['status'];
								}
							}else{
								$json['status'] = 'old';
							}
							
							if($shiptorPackage['status'] == 'new' && $this->config->get('shipping_shiptor_order_status_after_api_new') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_new'));
							}elseif($shiptorPackage['status'] == 'waiting-pickup' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup'));
							}elseif($shiptorPackage['status'] == 'waiting-send' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_send'));
							}elseif($shiptorPackage['status'] == 'arrived-to-warehouse' && $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse'));
							}elseif($shiptorPackage['status'] == 'packed' && $this->config->get('shipping_shiptor_order_status_after_api_packed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_packed'));
							}elseif($shiptorPackage['status'] == 'prepared-to-send' && $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send'));
							}elseif($shiptorPackage['status'] == 'sent' && $this->config->get('shipping_shiptor_order_status_after_api_sent') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_sent'));
							}elseif($shiptorPackage['status'] == 'delivered' && $this->config->get('shipping_shiptor_order_status_after_api_delivered') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_delivered'));
							}elseif($shiptorPackage['status'] == 'waiting-on-delivery-point' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point'));
							}elseif($shiptorPackage['status'] == 'removed' && $this->config->get('shipping_shiptor_order_status_after_api_removed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_removed'));
							}elseif($shiptorPackage['status'] == 'returned' && $this->config->get('shipping_shiptor_order_status_after_api_returned') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_returned'));
							}elseif($shiptorPackage['status'] == 'reported' && $this->config->get('shipping_shiptor_order_status_after_api_reported') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_reported'));
							}elseif($shiptorPackage['status'] == 'lost' && $this->config->get('shipping_shiptor_order_status_after_api_lost') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_lost'));
							}elseif($shiptorPackage['status'] == 'resend' && $this->config->get('shipping_shiptor_order_status_after_api_resend') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_resend'));
							}
						}else{
							$json['status'] = 'none';
						}
					}
				}
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function order_table() {
		$json = array();
		
		$this->load->language('extension/shipping/shiptor');

		if ($this->user->hasPermission('modify', 'extension/shipping/shiptor')) {
			$this->load->model('extension/shipping/shiptor');
			
			if($this->config->get('shipping_shiptor_city_delivery')){
				$data['shipping_shiptor_city_delivery'] = $this->config->get('shipping_shiptor_city_delivery');
			}else{
				$data['shipping_shiptor_city_delivery'] = '';
			}

			if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

				if (isset($this->request->post['type'], $this->request->post['order_id'])) {
					if ($this->request->post['type'] == 'remove' && $this->request->post['shiptor_status'] == 'new') {
						
						$shiptor = $this->model_extension_shipping_shiptor->getOrder($this->request->post['order_id']);
						
						$this->shiptor->removePackage($shiptor['shiptor_id']);
						$this->model_extension_shipping_shiptor->removeOrder($this->request->post['order_id']);

						$json['success'] = $this->language->get('success_remove');
					}elseif($this->request->post['type'] == 'remove' && $this->request->post['shiptor_status'] == 'old'){
						$this->model_extension_shipping_shiptor->removeOrder($this->request->post['order_id']);

						$json['success'] = $this->language->get('success_remove');
					}elseif($this->request->post['type'] == 'remove' && $this->request->post['shiptor_status'] == 'none'){
						$this->model_extension_shipping_shiptor->removeOrder($this->request->post['order_id']);

						$json['success'] = $this->language->get('success_remove');
					}

					if ($this->request->post['type'] == 'save') {
						$this->model_extension_shipping_shiptor->editOrder($this->request->post['order_id'], $this->request->post);

						$json['success'] = $this->language->get('success_update');
					}

					if ($this->request->post['type'] == 'shiptor') {
						$json = $this->setShiptor($this->request->post['order_id']);
						if(isset($json['success'])){
							if($json['status'] == 'new' && $this->config->get('shipping_shiptor_order_status_after_api_new') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_new'));
							}elseif($json['status'] == 'waiting-pickup' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup'));
							}elseif($json['status'] == 'waiting-send' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_send'));
							}elseif($json['status'] == 'arrived-to-warehouse' && $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse'));
							}elseif($json['status'] == 'packed' && $this->config->get('shipping_shiptor_order_status_after_api_packed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_packed'));
							}elseif($json['status'] == 'prepared-to-send' && $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send'));
							}elseif($json['status'] == 'sent' && $this->config->get('shipping_shiptor_order_status_after_api_sent') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_sent'));
							}elseif($json['status'] == 'delivered' && $this->config->get('shipping_shiptor_order_status_after_api_delivered') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_delivered'));
							}elseif($json['status'] == 'waiting-on-delivery-point' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point'));
							}elseif($json['status'] == 'removed' && $this->config->get('shipping_shiptor_order_status_after_api_removed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_removed'));
							}elseif($json['status'] == 'returned' && $this->config->get('shipping_shiptor_order_status_after_api_returned') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_returned'));
							}elseif($json['status'] == 'reported' && $this->config->get('shipping_shiptor_order_status_after_api_reported') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_reported'));
							}elseif($json['status'] == 'lost' && $this->config->get('shipping_shiptor_order_status_after_api_lost') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_lost'));
							}elseif($json['status'] == 'resend' && $this->config->get('shipping_shiptor_order_status_after_api_resend') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_resend'));
							}
						}
					}
					
					if($this->request->post['type'] == 'shipment') {
						$json = $this->setShiptorCourier($this->request->post['order_id']);
						if(isset($json['success'])){
							if($json['status'] == 'new' && $this->config->get('shipping_shiptor_order_status_after_api_new') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_new'));
							}elseif($json['status'] == 'waiting-pickup' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup'));
							}elseif($json['status'] == 'waiting-send' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_send'));
							}elseif($json['status'] == 'arrived-to-warehouse' && $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse'));
							}elseif($json['status'] == 'packed' && $this->config->get('shipping_shiptor_order_status_after_api_packed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_packed'));
							}elseif($json['status'] == 'prepared-to-send' && $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send'));
							}elseif($json['status'] == 'sent' && $this->config->get('shipping_shiptor_order_status_after_api_sent') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_sent'));
							}elseif($json['status'] == 'delivered' && $this->config->get('shipping_shiptor_order_status_after_api_delivered') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_delivered'));
							}elseif($json['status'] == 'waiting-on-delivery-point' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point'));
							}elseif($json['status'] == 'removed' && $this->config->get('shipping_shiptor_order_status_after_api_removed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_removed'));
							}elseif($json['status'] == 'returned' && $this->config->get('shipping_shiptor_order_status_after_api_returned') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_returned'));
							}elseif($json['status'] == 'reported' && $this->config->get('shipping_shiptor_order_status_after_api_reported') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_reported'));
							}elseif($json['status'] == 'lost' && $this->config->get('shipping_shiptor_order_status_after_api_lost') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_lost'));
							}elseif($json['status'] == 'resend' && $this->config->get('shipping_shiptor_order_status_after_api_resend') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($this->request->post['order_id'], $this->config->get('shipping_shiptor_order_status_after_api_resend'));
							}
						}
					}

					$this->response->addHeader('Content-Type: application/json');
					return $this->response->setOutput(json_encode($json));
				}
			}

			if (isset($this->request->get['filter_customer'])) {
				$filter_customer = $this->request->get['filter_customer'];
			} else {
				$filter_customer = null;
			}
			
			if (isset($this->request->get['filter_email'])) {
				$filter_email = $this->request->get['filter_email'];
			} else {
				$filter_email = null;
			}			
			
			if (isset($this->request->get['filter_telephone'])) {
				$filter_telephone = $this->request->get['filter_telephone'];
			} else {
				$filter_telephone = null;
			}			
			
			if (isset($this->request->get['filter_date_added'])) {
				$filter_date_added = $this->request->get['filter_date_added'];
			} else {
				$filter_date_added = null;
			}		

			if (isset($this->request->get['per_page'])) {
				$per_page = $this->request->get['per_page'];
			} else {
				$per_page = 10;
			}

			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}
			
			if (isset($this->request->get['filter_order_status'])) {
				$filter_order_status = $this->request->get['filter_order_status'];
			} else {
				$filter_order_status = null;
			}

			$filter_data = array(
				'filter_customer'   	=> $filter_customer,
				'filter_email'      	=> $filter_email,
				'filter_telephone'  	=> preg_replace("/(\D)/", "", urldecode($filter_telephone)),
				'filter_date_added'		=> $filter_date_added,
				'filter_order_status'	=> $filter_order_status,
				'start'             	=> ($page / $per_page) * $per_page,
				'limit'             	=> $per_page
			);

			$json['total'] = $this->model_extension_shipping_shiptor->getTotalOrders($filter_data);

			$json['rows'] = array();
			
			$orders = $this->model_extension_shipping_shiptor->getOrders($filter_data);
			
			if(!empty($data['shipping_shiptor_city_delivery']) && $this->config->get('shipping_shiptor_postponement_delivery')){
				$date_shipment_for_array = time() + $this->config->get('shipping_shiptor_postponement_delivery');
				$date_shipment_all = time() + $this->config->get('shipping_shiptor_postponement_delivery');
				$params['date_shipment'] = $date_shipment_all + 604800;
				$params['date_shipment_from'] = time();
				$shiptor = $this->shiptor->getDaysOffShipment($params);
				if(!empty($shiptor)){
					$i = 0;
					foreach($shiptor as $date){
						if(date('Y-m-d', $date_shipment_for_array) == $date){
							$date_shipment_for_array += 86400;
							$i++;
						}
					}
					$time_shiptor = $i * 86400;
					$date_shipment_all += $time_shiptor;
				}
			}

			if ($orders) {			
				foreach ($orders as $order) {			
					if ($order['street']) {
						$street = $order['street'];
					} else if ($order['shipping_address_1']) {
						$street = $order['shipping_address_1'];
					} else {
						$street = '';
					}

					$time = '';
					
					$times = $this->shiptor->getDeliveryTime();

					if ($times && is_array($times)) {
						foreach ($times as $key => $value) {
							if ($key == $order['time']) {
								$time = $value;
								break;
							}
						}
					}

					$payment = $order['payment_method'];
					
					$shipping_shipment = $this->model_extension_shipping_shiptor->getShippingMethod($order['shipping_method_int']);
					if($shipping_shipment){
						if($order['date_shipment'] !== '0000-00-00 00:00:00'){
							$date_shipment = strtotime($order['date_shipment']);
						}elseif(!empty($data['shipping_shiptor_city_delivery']) && $this->config->get('shipping_shiptor_postponement_delivery')){
							if(stristr($shipping_shipment['category'],'-to-')){
								$date_shipment = $date_shipment_all;
							}else{
								$date_shipment = '-';
							}
						}else{
							$date_shipment = '-';
						}
					}else{
						$date_shipment = '-';
					}
					
					$json['rows'][] = array(
						'order_id'   => (int)$order['order_id'],
						'shiptor_id' => (int)$order['shiptor_id']>0?(int)$order['shiptor_id']:'',
						'shipment_id' => (int)$order['shipment_id']>0?(int)$order['shipment_id']:'',
						'customer'   => $order['firstname'] . ' ' . $order['lastname'],
						'email'      => $order['email'],
						'telephone'  => $order['telephone'],
						'delivery'   => $order['shipping_method'],
						'time'	 	 => $time,
						'point_id'   => (int)$order['delivery_point'],
						'city'	 	 => $order['shipping_city'],
						'address'    => $order['address'],
						'kladr_id'   => $order['kladr_id'],
						'street'     => $street,
						'house'      => $order['house'],
						'apartment'  => $order['apartment'],
						'weight'     => (float)$order['weight'],
						'payment'    => $payment,
						'status'	 => $order['status'],
						'shipping_shiptor_city_delivery' => $data['shipping_shiptor_city_delivery'],
						'canApi'	 => ($order['order_status_id']==$this->config->get('shipping_shiptor_order_status_for_api')?true:false),
						'total'      => false !== strpos($order['payment_code'], 'shiptor_') ? round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency'))) : 0,
						'date_added' => date($this->language->get('date_format_short'), strtotime($order['date_added'])),
						'date_shipment' => ($date_shipment !== '-') ? date($this->language->get('date_format_short'), $date_shipment) : '-',
						'view'       => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order['order_id'], true),
						'edit'       => $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order['order_id'], true)
					);
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function comparisons(){
		
		$json = array();
		
		$this->load->language('extension/shipping/shiptor');
	
		//Кол-во отправлений
		$counts = 0;
	
		# array comparisons
		$data['setting'][0]['page'] = 1;
		$data['setting'][0]['per_page'] = 100;
		$data['setting'][1]['page'] = 2;
		$data['setting'][1]['per_page'] = 100;
		$data['setting'][2]['page'] = 3;
		$data['setting'][2]['per_page'] = 100;
		$data['setting'][3]['page'] = 4;
		$data['setting'][3]['per_page'] = 100;
		$data['setting'][4]['page'] = 5;
		$data['setting'][4]['per_page'] = 100;

		$this->load->model('extension/shipping/shiptor');
			
		$orders = array();
			
		foreach($data['setting'] as $key => $else){
			$params['per_page'] = $else['per_page'];
			$params['page']		= $else['page'];
			
			if(!empty($this->shiptor->getPackages($params))){
				$orders = $this->shiptor->getPackages($params);
					
				if ($orders) {
					foreach ($orders as $order) {
						if($this->model_extension_shipping_shiptor->getShiptorId($order['id'])){
							if($order['status'] == 'new' && $this->config->get('shipping_shiptor_order_status_after_api_new') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_new'));
								$counts++;
							}elseif($order['status'] == 'waiting-pickup' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup'));
								$counts++;
							}elseif($order['status'] == 'waiting-send' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_send'));
								$counts++;
							}elseif($order['status'] == 'arrived-to-warehouse' && $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse'));
								$counts++;
							}elseif($order['status'] == 'packed' && $this->config->get('shipping_shiptor_order_status_after_api_packed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_packed'));
								$counts++;
							}elseif($order['status'] == 'prepared-to-send' && $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send'));
								$counts++;
							}elseif($order['status'] == 'sent' && $this->config->get('shipping_shiptor_order_status_after_api_sent') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_sent'));
								$counts++;
							}elseif($order['status'] == 'delivered' && $this->config->get('shipping_shiptor_order_status_after_api_delivered') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_delivered'));
								$counts++;
							}elseif($order['status'] == 'waiting-on-delivery-point' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point'));
								$counts++;
							}elseif($order['status'] == 'removed' && $this->config->get('shipping_shiptor_order_status_after_api_removed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_removed'));
								$counts++;
							}elseif($order['status'] == 'returned' && $this->config->get('shipping_shiptor_order_status_after_api_returned') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_returned'));
								$counts++;
							}elseif($order['status'] == 'reported' && $this->config->get('shipping_shiptor_order_status_after_api_reported') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_reported'));
								$counts++;
							}elseif($order['status'] == 'lost' && $this->config->get('shipping_shiptor_order_status_after_api_lost') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_lost'));
								$counts++;
							}elseif($order['status'] == 'resend' && $this->config->get('shipping_shiptor_order_status_after_api_resend') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_resend'));
								$counts++;
							}
						}
					}
				}
			}
		}
		
		if($counts > 0){
			$json['success'] = "Статусы Shiptor получены.";
		}else{
			$json['error']['count'] = "Нет данных для обновлений";
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function shiptor_orders(){
		$json = array();
		$this->load->language('extension/shipping/shiptor');

		if ($this->user->hasPermission('modify', 'extension/shipping/shiptor')) {
			$this->load->model('extension/shipping/shiptor');
			
			if($this->config->get('shipping_shiptor_validate_period')){
				$json['orders'] = $this->model_extension_shipping_shiptor->getDateOrders($this->config->get('shipping_shiptor_validate_period'));
			}else{
				$json['orders'] = $this->model_extension_shipping_shiptor->getDateOrders(604800);
			}
			
			if(empty($json['orders'])){
				$json['error']['count'] = $this->language->get('error_is_count');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function shiptor_orders_courier(){
		$json = array();
		$this->load->language('extension/shipping/shiptor');

		if ($this->user->hasPermission('modify', 'extension/shipping/shiptor')) {
			$this->load->model('extension/shipping/shiptor');
			
			if($this->config->get('shipping_shiptor_validate_period')){
				$json['orders'] = $this->model_extension_shipping_shiptor->getDateOrdersCourier($this->config->get('shipping_shiptor_validate_period'));
				$json['orders_group'] = $this->model_extension_shipping_shiptor->getDateOrdersCourierGroup($this->config->get('shipping_shiptor_validate_period'));
			}else{
				$json['orders'] = $this->model_extension_shipping_shiptor->getDateOrdersCourier(604800);
				$json['orders_group'] = $this->model_extension_shipping_shiptor->getDateOrdersCourierGroup(604800);
			}
			
			$json['days'] = array();
			
			if($this->config->get('shipping_shiptor_postponement_delivery')){
				$date_shipment_for_array = time() + $this->config->get('shipping_shiptor_postponement_delivery');
				$date_shipment = time() + $this->config->get('shipping_shiptor_postponement_delivery');
				$dt['date_shipment'] = $date_shipment + 604800;
				$dt['date_shipment_from'] = time();
				$shiptor = $this->shiptor->getDaysOffShipment($dt);
				if(!empty($shiptor)){
					$i = 0;
					foreach($shiptor as $key => $date){
						if(date('Y-m-d', $date_shipment_for_array) == $date){
							$date_shipment_for_array += 86400;
							$i++;
						}
						$json['days'][$key] = date('d-m-Y',strtotime($date));
					}
					$time_shiptor = $i * 86400;
					$date_shipment += $time_shiptor;
				}
			}
			
			$json['date'] = date('d.m.Y', $date_shipment);
			
			$json['date_shipment'] = date('Y.m.d',$dt['date_shipment']);
			
			if(empty($json['orders'])){
				$json['error']['count'] = $this->language->get('error_is_count');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function send_to_shiptor_courier() {
		$this->load->language('extension/shipping/shiptor');
		$this->load->model('extension/shipping/shiptor');
		
		$json = array();

		if($this->request->post['key']) {
			list($type,$courier) = explode('.', $this->request->post['key']);
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
				
				$shipping_param = $this->model_extension_shipping_shiptor->getPvz('delivery-point-to-door',$courier);
				
				if ($this->config->get('shipping_shiptor_'. $shipping_param['id'] . '_pvz')){
					$params['shipment']['delivery_point'] = (int)$this->config->get('shipping_shiptor_'. $shipping_param['id'] . '_pvz');
				}else{
					$json['error']['delivery_point'] = $this->language->get('error_is_delivery_point');
				}
			}
			
			if($this->request->post['date']){
				$params['shipment']['date'] = date("d.m.Y", strtotime($this->request->post['date']));
			}elseif($this->config->get('shipping_shiptor_postponement_delivery')){
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
			
			foreach($this->request->post['orders'] as $key => $order){
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
					$this->model_extension_shipping_shiptor->editOrder($order['order_id'], array('street' => trim($order['shipping_address_1'])));

					$params['packages'][$key]['departure']['address']['address_line_1'] = trim($order['shipping_address_1']);
				}

					if ($order['apartment']) {
						$params['packages'][$key]['departure']['address']['apartment'] = trim($order['apartment']);
					}
					
					if ($order['shipping_postcode']) {
						if (iconv_strlen($order['shipping_postcode']) == 6 ) {
							$params['packages'][$key]['departure']['address']['postal_code'] = trim($order['shipping_postcode']);
						}else{
							$json['error']['postal_code'] = $this->language->get('error_is_postal_cod');
						}
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
				
				if(empty($order['lastname']) && empty($order['firstname'])){
					$json['error']['name'] = $this->language->get('error_is_name');
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

				if ($products = $this->model_extension_shipping_shiptor->productsOrder($order['order_id'])) {
					$this->load->model('catalog/product');

					// передаем номенклатуру

					foreach ($products as $product) {
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);

						// TODO: проверить, откуда данные - из product или product_info
						
						$shopArticle = '';
						$name = $product['name'];
						
						if(($this->config->get('shipping_shiptor_id_product')) && (!empty($product_info['sku']))){
							$shopArticle = $product_info['sku'];
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
							if(!empty($product_options)){
								foreach($product_options as $option){
									$shopArticle .= '-' . $option['value'];
									$name .= '-' . $option['value'];
								}
							}
						}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
							$shopArticle = $product['model'];
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
							if(!empty($product_options)){
								foreach($product_options as $option){
									$shopArticle .= '-' . $option['value'];
									$name .= '-' . $option['value'];
								}
							}
						}elseif(!empty($product['sku'])){
							$shopArticle = $product_info['sku'];
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
							if(!empty($product_options)){
								foreach($product_options as $option){
									$shopArticle .= '-' . $option['value'];
									$name .= '-' . $option['value'];
								}
							}
						}else{
							$shopArticle = $product['model'];
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
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

					if($this->config->get('shipping_shiptor_size')){
						$params['packages'][$key]['width'] = $this->config->get('shipping_shiptor_default_width');
						$params['packages'][$key]['length'] = $this->config->get('shipping_shiptor_default_length');
						$params['packages'][$key]['height'] = $this->config->get('shipping_shiptor_default_height');
					}elseif (1 == sizeof($products)) {
						$product = reset($products);
						
						if (1 == $product['quantity']) {
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);
							$params['packages'][$key] = array_merge($params['packages'][$key],$this->model_extension_shipping_shiptor->length($product_info));
							
							if($params['packages'][$key]['width'] > 0){
								$params['packages'][$key]['width'] = $params['packages'][$key]['width'];
							}else{
								$params['packages'][$key]['width'] = $this->config->get('shipping_shiptor_default_width');
							}
							
							if($params['packages'][$key]['length'] > 0){
								$params['packages'][$key]['length'] = $params['packages'][$key]['length'];
							}else{
								$params['packages'][$key]['length'] = $this->config->get('shipping_shiptor_default_length');
							}
							
							if($params['packages'][$key]['height'] > 0){
								$params['packages'][$key]['height'] = $params['packages'][$key]['height'];
							}else{
								$params['packages'][$key]['height'] = $this->config->get('shipping_shiptor_default_height');
							}
						}else{
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);
							$params['packages'][$key] = array_merge($params['packages'][$key],$this->model_extension_shipping_shiptor->length($product_info));
							if($params['packages'][$key]['width'] > 0){
								$width = $params['packages'][$key]['width'];
							}else{
								$width = $this->config->get('shipping_shiptor_default_width');
							}
							
							if($params['packages'][$key]['length'] > 0){
								$length = $params['packages'][$key]['length'];
							}else{
								$length = $this->config->get('shipping_shiptor_default_length');
							}
							
							if($params['packages'][$key]['height'] > 0){
								$height = $params['packages'][$key]['height'];
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
								$params['packages'][$key]['height'] = $sumVQ;
								$params['packages'][$key]['length'] = $sumVQ;
								$params['packages'][$key]['width'] = $sumVQ;
							}else{
								$params['packages'][$key]['length'] = $max_volume_product;
								$params['packages'][$key]['width'] = sqrt($sumV/$max_volume_product);
								$params['packages'][$key]['height'] = sqrt($sumV/$max_volume_product);
							}
						}
					}else{
						$sumV = 0;
						
						foreach($products as $product){
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);
							$params['packages'][$key] = array_merge($params['packages'][$key],$this->model_extension_shipping_shiptor->length($product_info));
							
							if($params['packages'][$key]['width'] > 0){
								$width = $params['packages'][$key]['width'];
							}else{
								$width = $this->config->get('shipping_shiptor_default_width');
							}
							
							if($params['packages'][$key]['length'] > 0){
								$length = $params['packages'][$key]['length'];
							}else{
								$length = $this->config->get('shipping_shiptor_default_length');
							}
							
							if($params['packages'][$key]['height'] > 0){
								$height = $params['packages'][$key]['height'];
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
							$params['packages'][$key]['height'] = $sumVQ;
							$params['packages'][$key]['length'] = $sumVQ;
							$params['packages'][$key]['width'] = $sumVQ;
						}else{
							$params['packages'][$key]['length'] = $max_volume_product;
							$params['packages'][$key]['width'] = sqrt($sumV/$max_volume_product);
							$params['packages'][$key]['height'] = sqrt($sumV/$max_volume_product);
						}
					}
					
					$params['packages'][$key]['length'] = ceil($params['packages'][$key]['length']);
					$params['packages'][$key]['width'] = ceil($params['packages'][$key]['width']);
					$params['packages'][$key]['height'] = ceil($params['packages'][$key]['height']);

					# Cписок продуктов
					$data['products'] = array();
					$params['packages'][$key]['products'] = array();

					$costProduct = 0;
					
					// Coupon
					$order_total = $this->model_extension_shipping_shiptor->totalOrder($order['order_id']);
					if(!empty($order_total)){
						preg_match('#\((.*?)\)#', $order_total['title'], $title);
						$coupon_info = $this->model_extension_shipping_shiptor->getCoupon($title[1]);
						$discount = (int)$coupon_info['discount'];
					}else{
						$discount = 0;
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
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
							if(!empty($product_options)){
								foreach($product_options as $option){
									$shopArticle .= '-' . $option['value'];
								}
							}
						}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
							$shopArticle = $product['model'];
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
							if(!empty($product_options)){
								foreach($product_options as $option){
									$shopArticle .= '-' . $option['value'];
								}
							}
						}elseif(!empty($product['sku'])){
							$shopArticle = $product_info['sku'];
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
							if(!empty($product_options)){
								foreach($product_options as $option){
									$shopArticle .= '-' . $option['value'];
								}
							}
						}else{
							$shopArticle = $product['model'];
							$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order['order_id'], $product['order_product_id']);
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
							'price' 	  => round($price, $this->currency->getDecimalPlace($this->config->get('config_currency')))
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
			
			if (! $json) {
				$shiptor = $this->shiptor->addPackages($params);
				if($this->request->post['check_sh'] == 'yes'){
					$this->shiptor->confirmShipment($shiptor['shipment']['id']);
				}
				$json['success'] = 'Отгрузка № ' . $shiptor['shipment']['id'] . ' добавлена';
				$db_date = date('Y-m-d', strtotime($params['shipment']['date']));
				foreach($shiptor['packages'] as $shiptor_s){
					
					if (! empty($shiptor_s['id'])) {
						$this->model_extension_shipping_shiptor->editOrder($shiptor_s['external_id'], array('shiptor_id' => $shiptor_s['id'],'shipment_id' => $shiptor['shipment']['id'], 'date_shipment' => $db_date));
						if($shiptor_s['status'] == 'new' && $this->config->get('shipping_shiptor_order_status_after_api_new') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_new'));
							}elseif($shiptor_s['status'] == 'waiting-pickup' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup'));
							}elseif($shiptor_s['status'] == 'waiting-send' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_send'));
							}elseif($shiptor_s['status'] == 'arrived-to-warehouse' && $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse'));
							}elseif($shiptor_s['status'] == 'packed' && $this->config->get('shipping_shiptor_order_status_after_api_packed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_packed'));
							}elseif($shiptor_s['status'] == 'prepared-to-send' && $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send'));
							}elseif($shiptor_s['status'] == 'sent' && $this->config->get('shipping_shiptor_order_status_after_api_sent') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_sent'));
							}elseif($shiptor_s['status'] == 'delivered' && $this->config->get('shipping_shiptor_order_status_after_api_delivered') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_delivered'));
							}elseif($shiptor_s['status'] == 'waiting-on-delivery-point' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point'));
							}elseif($shiptor_s['status'] == 'removed' && $this->config->get('shipping_shiptor_order_status_after_api_removed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_removed'));
							}elseif($shiptor_s['status'] == 'returned' && $this->config->get('shipping_shiptor_order_status_after_api_returned') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_returned'));
							}elseif($shiptor_s['status'] == 'reported' && $this->config->get('shipping_shiptor_order_status_after_api_reported') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_reported'));
							}elseif($shiptor_s['status'] == 'lost' && $this->config->get('shipping_shiptor_order_status_after_api_lost') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_lost'));
							}elseif($shiptor_s['status'] == 'resend' && $this->config->get('shipping_shiptor_order_status_after_api_resend') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($shiptor_s['external_id'], $this->config->get('shipping_shiptor_order_status_after_api_resend'));
							}
					}
				}
			} else {
				$json['error']['shiptor'] = $this->language->get('error_is_shiptor');
			}
		}else{
			$json['error']['shiptor'] = $this->language->get('error_is_shiptor');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}
	
	public function send_to_shiptor() {
		$this->load->language('extension/shipping/shiptor');
		$this->load->model('extension/shipping/shiptor');
		
		$json = array();
		
		if($this->request->post['order_id']){
			$order_id = $this->request->post['order_id'];
		}
		
		if ($order_id) {
				
			$params = array();
				
			$params['external_id'] = $order_id;
			
			$order = $this->model_extension_shipping_shiptor->getOrder($order_id);

			$params['delivery_time'] = $order['time'];

			$weight = trim($order['weight']);

			if ($weight && is_numeric($weight)) {
				$params['weight'] = (float)$weight;
			}

			$shipping_method = trim($order['shipping_method']);

			if ($shipping_method && is_numeric($shipping_method)) {
				$params['shipping_method'] = $shipping_method;
			} else {
				$json['error']['shipping'] = $this->language->get('error_is_shipping') . '. Номер заказа: ' . $order_id;
			}
				
			if(stristr($order['shipping_code'],'shiptor.russian-post-') && $order['lastname']==''){
				$json['error']['shipping'] = $this->language->get('error_is_surname') . '. Номер заказа: ' . $order_id;
			}

			$delivery_point = trim($order['delivery_point']);

			if ($delivery_point) {
				$params['delivery_point'] = $delivery_point;
			} else {
				if ($order['street']) {
					$params['street'] = trim($order['street']);
				} else if ($order['shipping_address_1']) {
					$this->model_extension_shipping_shiptor->editOrder($order_id, array('street' => trim($order['shipping_address_1'])));

					$params['street'] = trim($order['shipping_address_1']);
				}

				if ($order['house']) {
					$params['house'] = trim($order['house']);
				}

				if ($order['apartment']) {
					$params['apartment'] = trim($order['apartment']);
				}
					
				if ($order['shipping_postcode']) {
					$params['postal_code'] = trim($order['shipping_postcode']);
				}
			}

			if ($order['firstname']) {
				$params['name'] = $order['firstname'];
			}

			if ($order['lastname']) {
				$params['surname'] = $order['lastname']!=''?$order['lastname']:$order['firstname'];
			}
			else{
				$params['surname'] = $order['firstname'];
			}

			$email = utf8_strtolower(trim($order['email']));

			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$params['email'] = $email;
			} else {
				$json['error']['email'] = $this->language->get('error_is_email') . '. Номер заказа: ' . $order_id;
			}

			if ($order['telephone']) {
				if (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui', $order['telephone'])) {
					$params['phone'] = trim($order['telephone']);
				} else {
					$json['error']['telephone'] = $this->language->get('error_is_telephone') . '. Номер заказа: ' . $order_id;
				}
			}

			$kladr_id = trim($order['kladr_id']);

			if ($kladr_id) {
				$params['kladr_id'] = $kladr_id;
			} else {
				$json['error']['kladr_id'] = $this->language->get('error_is_kladr') . '. Номер заказа: ' . $order_id;
			}

			// вычисляем наложенный платеж и объявленную стоимость

			if ($order['total']) {
				$params['declared_cost'] = round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency')));

				$params['cod'] = (false !== strpos($order['payment_code'], 'shiptor_')) ? round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency'))) : 0;
			}

			if ($order['payment_code'] == 'shiptor_paycard') {
				$params['cashless_payment'] = 1;
			}
				
			// Пока отправляем только RU
			if ($order['country']) {
				$params['country_code'] = $order['country'];
			}
				
			if ($order['shipping_zone']) {
				$params['administrative_area'] = $order['shipping_zone'];
			}
				
			if ($order['shipping_city']) {
				$params['settlement'] = $order['shipping_city'];
			}

			// проверка наличия услуги с артикулом магазина (если нет - создаем)
			// артикул = url сайта
			$shop_article = mb_substr(HTTPS_CATALOG, 0, 64);

			if (!$this->shiptor->checkServiceByArticle($shop_article)) {
				$data = array('name' => $this->language->get('text_shipping'), 'shopArticle' => $shop_article, 'price' => 0);
				$this->shiptor->addService($data);
			}

			if ($products = $this->model_extension_shipping_shiptor->productsOrder($order_id)) {
				$this->load->model('catalog/product');

				// передаем номенклатуру

				foreach ($products as $product) {
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);
					// TODO: проверить, откуда данные - из product или product_info
					
					$shopArticle = '';
					$name = $product['name'];
					if(($this->config->get('shipping_shiptor_id_product')) && (!empty($product_info['sku']))){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}elseif(!empty($product['sku'])){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}else{
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
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

				if($this->config->get('shipping_shiptor_size')){
					$params['width'] = $this->config->get('shipping_shiptor_default_width');
					$params['length'] = $this->config->get('shipping_shiptor_default_length');
					$params['height'] = $this->config->get('shipping_shiptor_default_height');
				}elseif (1 == sizeof($products)) {
					$product = reset($products);
					
					if (1 == $product['quantity']) {
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product_info));
						
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
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product_info));
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
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product_info));
						
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

				# Cписок продуктов
				$params['products'] = array();
				
				// Coupon
				$order_total = $this->model_extension_shipping_shiptor->totalOrder($order_id);
				if(!empty($order_total)){
					preg_match('#\((.*?)\)#', $order_total['title'], $title);
					$coupon_info = $this->model_extension_shipping_shiptor->getCoupon($title[1]);
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
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}elseif(!empty($product['sku'])){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}else{
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}
					
					if (empty($product['quantity'])) {
						$json['error']['product'] = $this->language->get('error_is_quantity') . '. Номер заказа: ' . $order_id;
						continue;
					}

					if (empty($product['price'])) {
						$json['error']['product'] = $this->language->get('error_is_price') . '. Номер заказа: ' . $order_id;
						continue;
					}

					$params['products'][] = array(
						'shopArticle' => $shopArticle,
						'count'       => (int)$product['quantity'],
						'price'       => round($price, $this->currency->getDecimalPlace($this->config->get('config_currency')))
					);
				}
			}
				
			if (! $json) {
				$shiptor = $this->shiptor->addPackage($params);

				if (! empty($shiptor['id'])) {
					$this->model_extension_shipping_shiptor->editOrder($order_id, array('shiptor_id' => $shiptor['id']));
						
					$json['success'] = $this->language->get('success_shiptor') . '. Номер заказа: ' . $order_id;
					
					if(isset($json['success'])){
						if($shiptor['status'] == 'new' && $this->config->get('shipping_shiptor_order_status_after_api_new') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_new'));
							}elseif($shiptor['status'] == 'waiting-pickup' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_waiting_pickup'));
							}elseif($shiptor['status'] == 'waiting-send' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_waiting_send'));
							}elseif($shiptor['status'] == 'arrived-to-warehouse' && $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_arrived_to_warehouse'));
							}elseif($shiptor['status'] == 'packed' && $this->config->get('shipping_shiptor_order_status_after_api_packed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_packed'));
							}elseif($shiptor['status'] == 'prepared-to-send' && $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_prepared_to_send'));
							}elseif($shiptor['status'] == 'sent' && $this->config->get('shipping_shiptor_order_status_after_api_sent') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_sent'));
							}elseif($shiptor['status'] == 'delivered' && $this->config->get('shipping_shiptor_order_status_after_api_delivered') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_delivered'));
							}elseif($shiptor['status'] == 'waiting-on-delivery-point' && $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_waiting_on_delivery_point'));
							}elseif($shiptor['status'] == 'removed' && $this->config->get('shipping_shiptor_order_status_after_api_removed') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_removed'));
							}elseif($shiptor['status'] == 'returned' && $this->config->get('shipping_shiptor_order_status_after_api_returned') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_returned'));
							}elseif($shiptor['status'] == 'reported' && $this->config->get('shipping_shiptor_order_status_after_api_reported') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_reported'));
							}elseif($shiptor['status'] == 'lost' && $this->config->get('shipping_shiptor_order_status_after_api_lost') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_lost'));
							}elseif($shiptor['status'] == 'resend' && $this->config->get('shipping_shiptor_order_status_after_api_resend') > 0){
								$this->model_extension_shipping_shiptor->addOrderHistory($order_id, $this->config->get('shipping_shiptor_order_status_after_api_resend'));
							}
					}
						
				} else {
					$json['error']['shiptor'] = $this->language->get('error_is_shiptor') . '. Номер заказа: ' . $order_id;
				}
			}
		} else {
			$json['error']['order'] = $this->language->get('error_is_order');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	private function setShiptorCourier($order_id) {
		$this->load->language('extension/shipping/shiptor');

		$message = array();

		if ($order_id) {
			$params = array();
			
			$this->load->model('extension/shipping/shiptor');
			
			$order = $this->model_extension_shipping_shiptor->getOrder($order_id);
			
			$shipping_method = trim($order['shipping_method']);
			
			if ($shipping_method !==0 && is_numeric($shipping_method)) {
				$shipping_param = $this->model_extension_shipping_shiptor->getShippingMethod($shipping_method);
				if($shipping_param['category'] == 'door-to-delivery-point' || $shipping_param['category'] == 'door-to-door'){
					$params['shipment']['type'] = 'courier';
				}elseif($shipping_param['category'] == 'delivery-point-to-delivery-poi' || $shipping_param['category'] == 'delivery-point-to-door'){
					$params['shipment']['type'] = 'delivery-point';
				}else{
					$message['error']['shipping'] = $this->language->get('error_is_shipping');
				}
				$params['shipment']['courier'] = $shipping_param['courier'];
			} else {
				$message['error']['shipping'] = $this->language->get('error_is_shipping');
			}
			
			if($this->config->get('shipping_shiptor_fio_delivery')){
				$params['shipment']['address']['receiver'] =$this->config->get('shipping_shiptor_fio_delivery');
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
				$message['error']['email'] = $this->language->get('error_is_email');
			}
			
			if($this->config->get('shipping_shiptor_telephone_delivery')){
				if (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui',$this->config->get('shipping_shiptor_telephone_delivery'))) {
					$params['shipment']['address']['phone'] =$this->config->get('shipping_shiptor_telephone_delivery');
				}
			}elseif (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui', $this->config->get('config_telephone'))) {
				$params['shipment']['address']['phone'] = $this->config->get('config_telephone');
			}else{
				$message['error']['telephone'] = $this->language->get('error_is_telephone');
			}
			
			if($this->config->get('config_country_id')){
				$this->load->model('localisation/country');

				$country = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
				$params['shipment']['address']['country'] = $country['iso_code_2'];
			}else{
				$message['error']['country'] = $this->language->get('error_is_county');
			}
			
			
			if($this->config->get('shipping_shiptor_city_delivery')){
				$params['shipment']['address']['settlement'] =$this->config->get('shipping_shiptor_city_delivery');
			}else{
				$message['error']['city'] = $this->language->get('error_is_city');
			}
			
			if($this->config->get('shipping_shiptor_address_delivery')) {
				$params['shipment']['address']['street'] =$this->config->get('shipping_shiptor_address_delivery');
			}else{
				$message['error']['address'] = $this->language->get('error_is_address');
			}
			
			if($this->config->get('shipping_shiptor_city_delivery_value')){
				$params['shipment']['address']['kladr_id'] =$this->config->get('shipping_shiptor_city_delivery_value');
			}else{
				$message['error']['kladr_id'] = $this->language->get('error_is_kladr');
			}
			if($params['shipment']['type'] == 'delivery-point'){
				$shipping_prm = $this->model_extension_shipping_shiptor->getPvz('delivery-point-to-door',$shipping_param['courier']);

				if ($this->config->get('shipping_shiptor_'. $shipping_prm['id'] . '_pvz')){
					$params['shipment']['delivery_point'] = (int)$this->config->get('shipping_shiptor_'. $shipping_prm['id'] . '_pvz');
				}else{
					$message['error']['delivery_point'] = $this->language->get('error_is_delivery_point');
				}
			}
			
			
			if($this->config->get('shipping_shiptor_postponement_delivery')){
				$date_shipment_for_array = time() +$this->config->get('shipping_shiptor_postponement_delivery');
				$date_shipment = time() +$this->config->get('shipping_shiptor_postponement_delivery');
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
				$message['error']['date'] = $this->language->get('error_is_date');
			}
		
			// PACKAGE olso
			$params['packages'][0] = array();
			
			$params['packages'][0]['external_id'] = $order_id;
			
			$params['packages'][0]['length'] = 1;
			
			if ($this->config->get('shipping_shiptor_default_length')) {
				$params['packages'][0]['length'] = (float)$this->config->get('shipping_shiptor_default_length');
			}
			
			$params['packages'][0]['width'] = 1;
			
			if ($this->config->get('shipping_shiptor_default_width')) {
				$params['packages'][0]['width'] = (float)$this->config->get('shipping_shiptor_default_width');
			}
			
			$params['packages'][0]['height'] = 1;
			
			if ($this->config->get('shipping_shiptor_default_height')) {
				$params['packages'][0]['height'] = (float)$this->config->get('shipping_shiptor_default_height');
			}
			
			$weight = trim($order['weight']);

			if ($weight && is_numeric($weight)) {
				$params['packages'][0]['weight'] = (float)$weight;
			}else{
			
				$params['packages'][0]['weight'] = 1;
				
				if ($this->config->get('shipping_shiptor_default_weight')) {
					$params['packages'][0]['weight'] = (float)$this->config->get('shipping_shiptor_default_weight');
				}
			}

			if ($shipping_method && is_numeric($shipping_method)) {
				$params['packages'][0]['departure']['shipping_method'] = (int)$shipping_method;
			} else {
				$message['error']['shipping'] = $this->language->get('error_is_shipping');
			}
			
			if(stristr($order['shipping_code'],'shiptor.russian-post-') && $order['lastname']==''){
				$message['error']['shipping'] = $this->language->get('error_is_surname');
			}

			$delivery_point = trim($order['delivery_point']);

			if ($delivery_point) {
				$params['packages'][0]['departure']['delivery_point'] = $delivery_point;
			} else {
				if ($order['street']) {
					$params['packages'][0]['departure']['address']['address_line_1'] = trim($order['street']);
				} else if ($order['shipping_address_1']) {
					$this->model_extension_shipping_shiptor->editOrder($order_id, array('street' => trim($order['shipping_address_1'])));

					$params['packages'][0]['departure']['address']['address_line_1'] = trim($order['shipping_address_1']);
				}

				if ($order['apartment']) {
					$params['packages'][0]['departure']['address']['apartment'] = trim($order['apartment']);
				}
				
				if ($order['shipping_postcode']) {
					$params['packages'][0]['departure']['address']['postal_code'] = trim($order['shipping_postcode']);
				}
				
			}

			if ($order['firstname']) {
				$params['packages'][0]['departure']['address']['name'] = $order['firstname'];
			}

			if ($order['lastname']) {
				$params['packages'][0]['departure']['address']['surname'] =  $order['lastname']!=''?$order['lastname']:$order['firstname'];
			}
			else{
				$params['packages'][0]['departure']['address']['surname'] = $order['firstname'];
			}
			
			if(empty($order['lastname']) && empty($order['firstname'])){
				$message['error']['name'] = $this->language->get('error_is_name');
			}

			$email = utf8_strtolower(trim($order['email']));

			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$params['packages'][0]['departure']['address']['email'] = $email;
			} else {
				$message['error']['email'] = $this->language->get('error_is_email');
			}

			if ($order['telephone']) {
				if (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui', $order['telephone'])) {
					$params['packages'][0]['departure']['address']['phone'] = trim($order['telephone']);
				} else {
					$message['error']['telephone'] = $this->language->get('error_is_telephone');
				}
			}

			$kladr_id = trim($order['kladr_id']);

			if ($kladr_id) {
				$params['packages'][0]['departure']['address']['kladr_id'] = $kladr_id;
			} else {
				$message['error']['kladr_id'] = $this->language->get('error_is_kladr');
			}

			// вычисляем наложенный платеж и объявленную стоимость
			$params['packages'][0]['cod'] = 0;
			$params['packages'][0]['declared_cost'] = 10;
			
			if ($order['total']) {
				$params['packages'][0]['declared_cost'] = round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency')));

				$params['packages'][0]['cod'] = (false !== strpos($order['payment_code'], 'shiptor_')) ? round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency'))) : 0;
			
			}
			
			if($params['packages'][0]['declared_cost'] >= 10){
				$params['packages'][0]['declared_cost'] = $params['packages'][0]['declared_cost'];
			}else{
				$params['packages'][0]['declared_cost'] = 10;
			}

			if ($order['payment_code'] == 'shiptor_paycard') {
				$params['packages'][0]['departure']['cashless_payment'] = true;
			}
			
			// Пока отправляем только RU
			if ($order['country']) {
				$params['packages'][0]['departure']['address']['country'] = $order['country'];
			}
			
			if ($order['shipping_zone']) {
				$params['packages'][0]['departure']['address']['administrative_area'] = $order['shipping_zone'];
			}
			
			if ($order['shipping_city']) {
				$params['packages'][0]['departure']['address']['settlement'] = $order['shipping_city'];
			}

			// проверка наличия услуги с артикулом магазина (если нет - создаем)
			// артикул = url сайта

			$shop_article = mb_substr(HTTPS_CATALOG, 0, 64);

			if (!$this->shiptor->checkServiceByArticle($shop_article)) {
				$data = array('name' => $this->language->get('text_shipping'), 'shopArticle' => $shop_article, 'price' => 0);
				$this->shiptor->addService($data);
			}

			if ($products = $this->model_extension_shipping_shiptor->productsOrder($order_id)) {
				$this->load->model('catalog/product');

				// передаем номенклатуру

				foreach ($products as $product) {
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					// TODO: проверить, откуда данные - из product или product_info
					$shopArticle = '';
					$name = $product['name'];
					if(($this->config->get('shipping_shiptor_id_product')) && (!empty($product_info['sku']))){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}elseif(!empty($product['sku'])){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}else{
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
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

				if($this->config->get('shipping_shiptor_size')){
					$params['packages'][0]['width'] = $this->config->get('shipping_shiptor_default_width');
					$params['packages'][0]['length'] = $this->config->get('shipping_shiptor_default_length');
					$params['packages'][0]['height'] = $this->config->get('shipping_shiptor_default_height');
				}elseif (1 == sizeof($products)) {
					$product = reset($products);
					
					if (1 == $product['quantity']) {
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params['packages'][0] = array_merge($params['packages'][0],$this->model_extension_shipping_shiptor->length($product_info));
						
						if($params['packages'][0]['width'] > 0){
							$params['packages'][0]['width'] = $params['packages'][0]['width'];
						}else{
							$params['packages'][0]['width'] = $this->config->get('shipping_shiptor_default_width');
						}
						
						if($params['packages'][0]['length'] > 0){
							$params['packages'][0]['length'] = $params['packages'][0]['length'];
						}else{
							$params['packages'][0]['length'] = $this->config->get('shipping_shiptor_default_length');
						}
						
						if($params['packages'][0]['height'] > 0){
							$params['packages'][0]['height'] = $params['packages'][0]['height'];
						}else{
							$params['packages'][0]['height'] = $this->config->get('shipping_shiptor_default_height');
						}
					}else{
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params['packages'][0] = array_merge($params['packages'][0],$this->model_extension_shipping_shiptor->length($product_info));
						if($params['packages'][0]['width'] > 0){
							$width = $params['packages'][0]['width'];
						}else{
							$width = $this->config->get('shipping_shiptor_default_width');
						}
						
						if($params['packages'][0]['length'] > 0){
							$length = $params['packages'][0]['length'];
						}else{
							$length = $this->config->get('shipping_shiptor_default_length');
						}
						
						if($params['packages'][0]['height'] > 0){
							$height = $params['packages'][0]['height'];
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
							$params['packages'][0]['height'] = $sumVQ;
							$params['packages'][0]['length'] = $sumVQ;
							$params['packages'][0]['width'] = $sumVQ;
						}else{
							$params['packages'][0]['length'] = $max_volume_product;
							$params['packages'][0]['width'] = sqrt($sumV/$max_volume_product);
							$params['packages'][0]['height'] = sqrt($sumV/$max_volume_product);
						}
					}
				}else{
					$sumV = 0;
					
					foreach($products as $product){
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params['packages'][0] = array_merge($params['packages'][0],$this->model_extension_shipping_shiptor->length($product_info));
						
						if($params['packages'][0]['width'] > 0){
							$width = $params['packages'][0]['width'];
						}else{
							$width = $this->config->get('shipping_shiptor_default_width');
						}
						
						if($params['packages'][0]['length'] > 0){
							$length = $params['packages'][0]['length'];
						}else{
							$length = $this->config->get('shipping_shiptor_default_length');
						}
						
						if($params['packages'][0]['height'] > 0){
							$height = $params['packages'][0]['height'];
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
						$params['packages'][0]['height'] = $sumVQ;
						$params['packages'][0]['length'] = $sumVQ;
						$params['packages'][0]['width'] = $sumVQ;
					}else{
						$params['packages'][0]['length'] = $max_volume_product;
						$params['packages'][0]['width'] = sqrt($sumV/$max_volume_product);
						$params['packages'][0]['height'] = sqrt($sumV/$max_volume_product);
					}
				}
				
				$params['packages'][0]['length'] = ceil($params['packages'][0]['length']);
				$params['packages'][0]['width'] = ceil($params['packages'][0]['width']);
				$params['packages'][0]['height'] = ceil($params['packages'][0]['height']);

				# Cписок продуктов
				$data['products'] = array();
				$params['packages'][0]['products'] = array();

				$costProduct = 0;
				
				// Coupon
				$order_total = $this->model_extension_shipping_shiptor->totalOrder($order_id);
				if(!empty($order_total)){
					preg_match('#\((.*?)\)#', $order_total['title'], $title);
					$coupon_info = $this->model_extension_shipping_shiptor->getCoupon($title[1]);
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
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}elseif(!empty($product['sku'])){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}else{
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}
					
					if (empty($product['quantity'])) {
						$message['error']['product'] = $this->language->get('error_is_quantity');
						continue;
					}

					if (empty($product['price'])) {
						$message['error']['product'] = $this->language->get('error_is_price');
						continue;
					}

					$costProduct += (int)$product['quantity']*round($price, $this->currency->getDecimalPlace($this->config->get('config_currency')));
					$costProduct = round($costProduct, 0);
					
					$params['packages'][0]['products'][] = array(
						'shopArticle' => $shopArticle,
						'count' 	  => $product['quantity'],
						'price' 	  =>  round($price, $this->currency->getDecimalPlace($this->config->get('config_currency')))
					);
				}
				
				if (0 != $params['packages'][0]['cod']) {
					$params['packages'][0]['services'][] = array(
						'shopArticle'	=> mb_substr(HTTPS_CATALOG, 0, 64), // артикул услуги = url сайта (к этому моменту уже создана в ЛК шиптора)
						'price'		=> round($params['packages'][0]['cod']  - $costProduct, $this->currency->getDecimalPlace($this->config->get('config_currency'))),
						'count'		=> 1,
					);
				}
			}

			if (!$message) {
				$shiptor = $this->shiptor->addPackages($params);

				if (! empty($shiptor['packages'][0]['id'])) {
					$db_date = date('Y-m-d', strtotime($params['shipment']['date']));
					$this->model_extension_shipping_shiptor->editOrder($order_id, array('shiptor_id' => $shiptor['packages'][0]['id'],'shipment_id' => $shiptor['shipment']['id'], 'date_shipment' => $db_date));

					$message['success'] = $this->language->get('success_shiptor');
					$message['status'] = $shiptor['packages'][0]['status'];
				} else {
					$message['error']['shiptor'] = $this->language->get('error_is_shiptor');
				}
			}
			
		} else {
			$message['error']['order'] = $this->language->get('error_is_order');
		}
		
		return $message;
	}
	
	private function setShiptor($order_id) {
		$this->load->language('extension/shipping/shiptor');

		$message = array();

		if ($order_id) {
			$params = array();

			$this->load->model('extension/shipping/shiptor');


			$params['external_id'] = $order_id;

			$order = $this->model_extension_shipping_shiptor->getOrder($order_id);

			$params['delivery_time'] = $order['time'];

			$weight = trim($order['weight']);

			if ($weight && is_numeric($weight)) {
				$params['weight'] = (float)$weight;
			}

			$shipping_method = trim($order['shipping_method']);

			if ($shipping_method && is_numeric($shipping_method)) {
				$params['shipping_method'] = $shipping_method;
			} else {
				$message['error']['shipping'] = $this->language->get('error_is_shipping');
			}
			
			if(stristr($order['shipping_code'],'shiptor.russian-post-') && $order['lastname']==''){
				$message['error']['shipping'] = $this->language->get('error_is_surname');
			}

			$delivery_point = trim($order['delivery_point']);

			if ($delivery_point) {
				$params['delivery_point'] = $delivery_point;
			} else {
				if ($order['street']) {
					$params['street'] = trim($order['street']);
				} else if ($order['shipping_address_1']) {
					$this->model_extension_shipping_shiptor->editOrder($order_id, array('street' => trim($order['shipping_address_1'])));

					$params['street'] = trim($order['shipping_address_1']);
				}

				if ($order['house']) {
					$params['house'] = trim($order['house']);
				}

				if ($order['apartment']) {
					$params['apartment'] = trim($order['apartment']);
				}
				
				if ($order['shipping_postcode']) {
					$params['postal_code'] = trim($order['shipping_postcode']);
				}
			}

			if ($order['firstname']) {
				$params['name'] = $order['firstname'];
			}

			if ($order['lastname']) {
				$params['surname'] = $order['lastname']!=''?$order['lastname']:$order['firstname'];
			}
			else{
				$params['surname'] = $order['firstname'];
			}

			$email = utf8_strtolower(trim($order['email']));

			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$params['email'] = $email;
			} else {
				$message['error']['email'] = $this->language->get('error_is_email');
			}

			if ($order['telephone']) {
				if (preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/ui', $order['telephone'])) {
					$params['phone'] = trim($order['telephone']);
				} else {
					$message['error']['telephone'] = $this->language->get('error_is_telephone');
				}
			}

			$kladr_id = trim($order['kladr_id']);

			if ($kladr_id) {
				$params['kladr_id'] = $kladr_id;
			} else {
				$message['error']['kladr_id'] = $this->language->get('error_is_kladr');
			}

			// вычисляем наложенный платеж и объявленную стоимость

			if ($order['total']) {
				$params['declared_cost'] = round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency')));

				$params['cod'] = (false !== strpos($order['payment_code'], 'shiptor_')) ? round($order['total'], $this->currency->getDecimalPlace($this->config->get('config_currency'))) : 0;
			}

			if ($order['payment_code'] == 'shiptor_paycard') {
				$params['cashless_payment'] = 1;
			}
			
			// Пока отправляем только RU
			if ($order['country']) {
				$params['country_code'] = $order['country'];
			}
			
			if ($order['shipping_zone']) {
				$params['administrative_area'] = $order['shipping_zone'];
			}
			
			if ($order['shipping_city']) {
				$params['settlement'] = $order['shipping_city'];
			}

			// проверка наличия услуги с артикулом магазина (если нет - создаем)
			// артикул = url сайта

			$shop_article = mb_substr(HTTPS_CATALOG, 0, 64);

			if (!$this->shiptor->checkServiceByArticle($shop_article)) {
				$data = array('name' => $this->language->get('text_shipping'), 'shopArticle' => $shop_article, 'price' => 0);
				$this->shiptor->addService($data);
			}

			if ($products = $this->model_extension_shipping_shiptor->productsOrder($order_id)) {
				$this->load->model('catalog/product');

				// передаем номенклатуру

				foreach ($products as $product) {
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					// TODO: проверить, откуда данные - из product или product_info
					$shopArticle = '';
					$name = $product['name'];
					if(($this->config->get('shipping_shiptor_id_product')) && (!empty($product_info['sku']))){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}elseif(!empty($product['sku'])){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
								$name .= '-' . $option['value'];
							}
						}
					}else{
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
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

				if($this->config->get('shipping_shiptor_size')){
					$params['width'] = $this->config->get('shipping_shiptor_default_width');
					$params['length'] = $this->config->get('shipping_shiptor_default_length');
					$params['height'] = $this->config->get('shipping_shiptor_default_height');
				}elseif (1 == sizeof($products)) {
					$product = reset($products);
					
					if (1 == $product['quantity']) {
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product_info));
						
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
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product_info));
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
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$params = array_merge($params,$this->model_extension_shipping_shiptor->length($product_info));
						
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

				# Cписок продуктов
				$params['products'] = array();
				
				// Coupon
				$order_total = $this->model_extension_shipping_shiptor->totalOrder($order_id);
				if(!empty($order_total)){
					preg_match('#\((.*?)\)#', $order_total['title'], $title);
					$coupon_info = $this->model_extension_shipping_shiptor->getCoupon($title[1]);
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
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}elseif(($this->config->get('shipping_shiptor_id_product') == 0) && (!empty($product['model']))){
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}elseif(!empty($product['sku'])){
						$shopArticle = $product_info['sku'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}else{
						$shopArticle = $product['model'];
						$product_options = $this->model_extension_shipping_shiptor->productOptionsOrder($order_id, $product['order_product_id']);
						if(!empty($product_options)){
							foreach($product_options as $option){
								$shopArticle .= '-' . $option['value'];
							}
						}
					}
					
					if (empty($product['quantity'])) {
						$message['error']['product'] = $this->language->get('error_is_quantity');
						continue;
					}

					if (empty($product['price'])) {
						$message['error']['product'] = $this->language->get('error_is_price');
						continue;
					}

					$params['products'][] = array(
						'shopArticle' => $shopArticle,
						'count'       => (int)$product['quantity'],
						'price'       => round($price, $this->currency->getDecimalPlace($this->config->get('config_currency')))
					);
				}
			}

			if (! $message) {
				$shiptor = $this->shiptor->addPackage($params);

				if (! empty($shiptor['id'])) {
					$this->model_extension_shipping_shiptor->editOrder($order_id, array('shiptor_id' => $shiptor['id']));

					$message['success'] = $this->language->get('success_shiptor');
					$message['status'] = $shiptor['status'];
				} else {
					$message['error']['shiptor'] = $this->language->get('error_is_shiptor');
				}
			}
		} else {
			$message['error']['order'] = $this->language->get('error_is_order');
		}

		return $message;
	}

	
	
	private function validate() {	
		if (! $this->user->hasPermission('modify', 'extension/shipping/shiptor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (! extension_loaded('curl')) {
			$this->error['warning'] = $this->language->get('error_extension');
		}

		if (empty($this->request->post['shipping_shiptor_authorization'])) {
			$this->error['error']['authorization'] = $this->language->get('error_authorization');
		}
		
		// при активации модуля данные еще не передаются, но валидация уже идет (?!), поэтому используем isset на переменные
		
		if (isset($this->request->post['shipping_shiptor_weight'])) {
			if (empty($this->request->post['shipping_shiptor_weight'])) {
				$this->error['error']['default_weight'] = $this->language->get('error_default_weight');
			}
		}

		if (isset($this->request->post['shipping_shiptor_default_length'], $this->request->post['shipping_shiptor_default_width'], $this->request->post['shipping_shiptor_default_height'])) {
			if (empty($this->request->post['shipping_shiptor_default_length'])
			&& empty($this->request->post['shipping_shiptor_default_width'])
			&& empty($this->request->post['shipping_shiptor_default_height'])) {
				$this->error['error']['default_dimensions'] = $this->language->get('error_default_dimensions');
			}
		}
		
		foreach($this->shiptor->getShippingMethods() as $shipping_method){
			if(isset($this->request->post['shipping_shiptor_'.$shipping_method['id'].'_type']) && $this->request->post['shipping_shiptor_'.$shipping_method['id'].'_type']=='P' && !empty($this->request->post['shiptor_'.$shipping_method['id'].'_markup']) &&
				((int)$this->request->post['shipping_shiptor_'.$shipping_method['id'].'_markup']>100 || (int)$this->request->post['shipping_shiptor_'.$shipping_method['id'].'_markup']<-100)){
				$this->error['error'][$shipping_method['id'].'_markup'] = $this->language->get('error_is_markup');
			}
			if(isset($this->request->post['shipping_shiptor_'.$shipping_method['id'].'_type']) && $this->request->post['shipping_shiptor_'.$shipping_method['id'].'_type']=='P' && !empty($this->request->post['shiptor_'.$shipping_method['id'].'_markup_surcharge']) &&
				((int)$this->request->post['shipping_shiptor_'.$shipping_method['id'].'_markup_surcharge']>100 || (int)$this->request->post['shipping_shiptor_'.$shipping_method['id'].'_markup_surcharge']<-100)){
				$this->error['error'][$shipping_method['id'].'_markup_surcharge'] = $this->language->get('error_is_markup');
			}
		}
		return ! $this->error;	
	}

	public function regions() {
		$json = array();

		if (isset($this->request->get['filter_name']) && $this->user->hasPermission('modify', 'extension/shipping/shiptor')) {
			
			$this->load->model('extension/shipping/shiptor');	

			$data = array('filter_name' => $this->request->get['filter_name'], 'start' => 0, 'limit' => 20);
			
			$results = $this->model_extension_shipping_shiptor->getRegions($data);

			foreach ($results as $result) {
				$json[] = array('id' => $result['id'], 'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')));					
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function auth() {
		$this->load->language('extension/shipping/shiptor');
		
		$json = array();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'extension/shipping/shiptor')) {			
			if (isset($this->request->post['shipping_shiptor_authorization'])) {
				$this->load->model('setting/setting');
				
				$store_id = 0;
				$code = 'shipping_shiptor';
				$key = 'shipping_shiptor_authorization';
				
				$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND  `key` = '" . $this->db->escape($key) . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($this->request->post['shipping_shiptor_authorization']) . "'");
				
				$this->config->set('shipping_shiptor_authorization',$this->request->post['shipping_shiptor_authorization']);
			}
			
			if ($this->config->get('shipping_shiptor_authorization')) {
				$this->load->model('extension/shipping/shiptor');

				$shipping_methods = $this->shiptor->getShippingMethods();
				
				if ($shipping_methods) {
					$this->model_extension_shipping_shiptor->setShippingMethods($shipping_methods);

					$сountries = $this->shiptor->getCountries();
					$this->model_extension_shipping_shiptor->setCountries($сountries);

					$json['success'] = true;
				} else {
					$json['error'] = $this->language->get('error_key');
				}
			} else {
				$json['error'] = $this->language->get('error_setting');
			}
		} else {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install() {
		$this->load->model('extension/shipping/shiptor');	
		$this->model_extension_shipping_shiptor->install();
	}
	
	public function uninstall() {
		$this->load->model('extension/shipping/shiptor');
		$this->model_extension_shipping_shiptor->uninstall();
	}
}