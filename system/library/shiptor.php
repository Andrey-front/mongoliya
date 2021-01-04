<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class Shiptor {
	private $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function __get($name) {
		return $this->registry->get($name);
	}

	# Shipping - addPackage — Добавление посылки
	final public function addPackage($data = array()) {
		$params = array();
		
		# Длина, см
		# Number
		$params['length'] = 1;
		
		if (isset($data['length']) && 0 != (float)$data['length']) {
			$params['length'] = (float)$data['length'];
		} elseif ($this->config->get('shipping_shiptor_default_length')) {
			$params['length'] = (float)$this->config->get('shipping_shiptor_default_length');
		}

		# Ширина, см
		# Number
		$params['width'] = 1;

		if (isset($data['width']) && 0 != (float)$data['width']) {
			$params['width'] = (float)$data['width'];
		} elseif ($this->config->get('shipping_shiptor_default_width')) {
			$params['width'] = (float)$this->config->get('shipping_shiptor_default_width');
		}
			
		# Высота, см
		# Number
		
		$params['height'] = 1;
		
		if (isset($data['height']) && 0 != (float)$data['height']) {
			$params['height'] = (float)$data['height'];
		} elseif ($this->config->get('shipping_shiptor_default_height')) {
			$params['height'] = (float)$this->config->get('shipping_shiptor_default_height');
		}
  		
		# Вес, кг
		# Number
		$params['weight'] = 1;
		
		if (isset($data['weight']) && 0 != (float)$data['weight']) {
			$params['weight'] = (float)$data['weight'];
		}
		
		# Сумма наложенного платежа, руб. Если отсутствует - передавайте 0
		# # Объявленная ценность, руб. Если отсутствует - передавайте 0. Если есть наложенный платеж, то он должен быть равен объявленной ценности.
		# Number

		$params['cod'] = 0;
		
		if (! empty($data['cod'])) {
			$params['cod'] = $data['cod'];
		}
		
		$params['declared_cost'] = 10;
		
		if (! empty($data['declared_cost'])) {
			if($data['declared_cost'] >= 10){
				$params['declared_cost'] = $data['declared_cost'];
			}else{
				$params['declared_cost'] = 10;
			}
		}
		
		# Не собирать посылку (только фулфилмент) По умолчанию: false
		# Boolean
		# необязательный
		$params['no_gather'] = false;
		
		if (! empty($data['no_gather'])) {
			$params['no_gather'] = true;
		}
		
		# Уникальный идентификатор заказа в вашем магазине
		# String		
		# необязательный		
		if (! empty($data['external_id'])) {
			$params['external_id'] = $data['external_id'];
		}
		
		# Данные об отправлении
		$params['departure'] = array();

		# ID способа доставки
		# Number
		if (! empty($data['shipping_method'])) {
			$params['departure']['shipping_method'] = (int)$data['shipping_method'];
		}

		# ID пункта самовывоза
		# String
		# необязательный
		if (! empty($data['delivery_point'])) {
			$params['departure']['delivery_point'] = (int)$data['delivery_point'];
		}
		
		# Рекомендованное время доставки Допустимые значения: "смотреть метод getDeliveryTime"
		# Number
		# необязательный
		if (! empty($data['delivery_time'])) {
			$params['departure']['delivery_time'] = (int)$data['delivery_time'];
		}
		
		if (! empty($data['cashless_payment'])) {
			$params['departure']['cashless_payment'] = true;
		}
		
		$params['departure']['comment'] = '';

		# Данные об адресе доставки
		$params['departure']['address'] = array();

		# Страна Допустимые значения: "RU"
		# String
		
		$params['departure']['address']['country'] = 'RU';
		
		if (! empty($data['country_code'])) {
			$params['departure']['address']['country'] = $data['country_code'];
		}
		else {
			if ($this->config->get('config_country_id')) {
				$this->load->model('localisation/country');
				$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
				if(!empty($country_info))$params['departure']['address']['country'] = $country_info['iso_code_2'];
			}
		}
		
		# Имя
		# String
		if (! empty($data['name'])) {
			$params['departure']['address']['name'] = $data['name'];
		}	
		
		# Фамилия
		# String
		if (! empty($data['surname'])) {
			$params['departure']['address']['surname'] = $data['surname'];
		}
		
		# Отчество
		# String
		# необязательный
		if (! empty($data['patronymic'])) {
			$params['departure']['address']['patronymic'] = $data['patronymic'];
		}

		# Адрес электронной почты
		# String
		if (! empty($data['email'])) {
			$params['departure']['address']['email'] = $data['email'];
		}

		# Номер телефона в международном формате (+7…)
		# String
		if (! empty($data['phone'])) {
			$params['departure']['address']['phone'] = $data['phone'];
		}

		# Почтовый индекс
		# String
		if (! empty($data['postal_code'])) {
			$params['departure']['address']['postal_code'] = $data['postal_code'];
		}

		# Область. Можно оставить пустой, если передан kladr_id.
		# String
		# необязательный
		if (! empty($data['administrative_area'])) {
			$params['departure']['address']['administrative_area'] = $data['administrative_area'];
		}

		# Населенный пункт. Можно оставить пустым, если передан kladr_id.
		# String
		# необязательный
		if (! empty($data['settlement'])) {
			$params['departure']['address']['settlement'] = $data['settlement'];
		}

		# Улица
		# String
		if (! empty($data['street'])) {
			$params['departure']['address']['address_line_1'] = $data['street'];
		}

		# Дом
		# String
		if (! empty($data['house'])) {
			$params['departure']['address']['house'] = $data['house'];
		}

		# Квартира
		# String
		if (! empty($data['apartment'])) {
			$params['departure']['address']['apartment'] = $data['apartment'];
		}

		# Код КЛАДР населенного пункта, можно получить из справочника населенных пунктов. При отсутствии будет определен автоматически.
		# String
		# необязательный
		if (! empty($data['kladr_id'])) {
			$params['departure']['address']['kladr_id'] = $data['kladr_id'];
		}
		
		# Cписок продуктов 
		# Array
		$this->load->language('extension/shipping/shiptor');
		$costProduct = 0;

		if (! empty($data['products'])) {
			$params['products'] = array();
			
			foreach ($data['products'] as $product) {
				$costProduct += $product['count']*$product['price'];
				$params['products'][] = array(
					'shopArticle' => $product['shopArticle'],
					'count' 	  => $product['count'],
					'price' 	  => $product['price']
				);
			}
		}

		if (0 != $params['cod']) {
			$params['services'][] = array(
				'shopArticle'	=> mb_substr(HTTPS_CATALOG, 0, 64), // артикул услуги = url сайта (к этому моменту уже создана в ЛК шиптора)
				'price'		=> $params['cod']  - $costProduct,
				'count'		=> 1,
			);
		}
		
		return $this->request('addPackage', $params);
	}
	
	# Shipping - addPackages (courier and delivery_point) — Добавить несколько пакетов c забором курьерской службы
	final public function addPackages($data = array()) {
		$params = array();
		
		# Тип добавления с забором курьерской службы
		# String
		if (! empty($data['shipment']['type'])) {
			$params['shipment']['type'] = $data['shipment']['type'];
		}
		
		# Имя курьера
		# String
		if (! empty($data['shipment']['courier'])) {
			$params['shipment']['courier'] = $data['shipment']['courier'];
		}
		
		# Дата отправления (формат d.m.Y)
		# String
		if (! empty($data['shipment']['date'])) {
			$params['shipment']['date'] = $data['shipment']['date'];
		}
		
		# ФИО или организация отправитель
		# String
		$params['shipment']['address']['receiver'] = $this->config->get('config_owner');
		if (! empty($data['shipment']['address']['receiver'])) {
			$params['shipment']['address']['receiver'] = $data['shipment']['address']['receiver'];
		}
		
		# Телефон отправителя
		# String
		$params['shipment']['address']['phone'] = $this->config->get('config_telephone');
		if (! empty($data['shipment']['address']['phone'])) {
			$params['shipment']['address']['phone'] = $data['shipment']['address']['phone'];
		}
		
		
		# Email отправителя
		# String
		$params['shipment']['address']['email'] = $this->config->get('config_email');
		if (! empty($data['shipment']['address']['email'])) {
			$params['shipment']['address']['email'] = $data['shipment']['address']['email'];
		}
		
		# Номер пункта приёма
		# String
		if($params['shipment']['type'] == 'delivery-point' && !empty($data['shipment']['delivery_point'])){
			$params['shipment']['delivery_point'] = $data['shipment']['delivery_point'];
		}
		
		if($params['shipment']['type'] == 'courier'){
			
			# Страна
			# Допустимые значения: "RU", "KZ", "BY"
			# String
			$params['shipment']['address']['country'] = 'RU';
			if (! empty($data['shipment']['address']['country'])) {
				$params['shipment']['address']['country'] = $data['shipment']['address']['country'];
			}
			
			# Область
			# String
			if (! empty($data['shipment']['address']['administrative_area'])) {
				$params['shipment']['address']['administrative_area'] = $data['shipment']['address']['administrative_area'];
			}
			
			# Город
			# String
			$params['shipment']['address']['settlement'] = 'Москва';
			if (! empty($data['shipment']['address']['settlement'])) {
				$params['shipment']['address']['settlement'] = $data['shipment']['address']['settlement'];
			}
			
			# Улица
			# String
			if (! empty($data['shipment']['address']['street'])) {
				$params['shipment']['address']['street'] = $data['shipment']['address']['street'];
			}
			
			# Дом
			# String
			if (! empty($data['shipment']['address']['house'])) {
				$params['shipment']['address']['house'] = $data['shipment']['address']['house'];
			}
			
			# kladr
			# String
			if (! empty($data['shipment']['address']['kladr_id'])) {
				$params['shipment']['address']['kladr_id'] = $data['shipment']['address']['kladr_id'];
			}
			
		}
		
		
		$params['packages'] = $data['packages'];
		
		return $this->request('addPackages', $params);
	}
	
	# Shipping - editPackage — Редактирование посылки
	final public function editPackage($data = array()) {
		$params = array();
		
		# Уникальный идентификатор заказа в Shiptor
		# String	
		if (! empty($data['id'])) {
			$params['id'] = $data['id'];
		}
		
		# Длина, см
		# Number
		$params['length'] = 1;
		
		if (isset($data['length']) && 0 != (float)$data['length']) {
			$params['length'] = (float)$data['length'];
		} elseif ($this->config->get('shipping_shiptor_default_length')) {
			$params['length'] = (float)$this->config->get('shipping_shiptor_default_length');
		}

		# Ширина, см
		# Number
		$params['width'] = 1;

		if (isset($data['width']) && 0 != (float)$data['width']) {
			$params['width'] = (float)$data['width'];
		} elseif ($this->config->get('shipping_shiptor_default_width')) {
			$params['width'] = (float)$this->config->get('shipping_shiptor_default_width');
		}
			
		# Высота, см
		# Number
		
		$params['height'] = 1;
		
		if (isset($data['height']) && 0 != (float)$data['height']) {
			$params['height'] = (float)$data['height'];
		} elseif ($this->config->get('shipping_shiptor_default_height')) {
			$params['height'] = (float)$this->config->get('shipping_shiptor_default_height');
		}
  		
		# Вес, кг
		# Number
		$params['weight'] = 1;
		
		if (isset($data['weight']) && 0 != (float)$data['weight']) {
			$params['weight'] = (float)$data['weight'];
		}
		
		# Сумма наложенного платежа, руб. Если отсутствует - передавайте 0
		# # Объявленная ценность, руб. Если отсутствует - передавайте 0. Если есть наложенный платеж, то он должен быть равен объявленной ценности.
		# Number

		$params['cod'] = 0;
		
		if (! empty($data['cod'])) {
			$params['cod'] = $data['cod'];
		}
		
		$params['declared_cost'] = 10;
		
		if (! empty($data['declared_cost'])) {
			if($data['declared_cost'] >= 10){
				$params['declared_cost'] = $data['declared_cost'];
			}else{
				$params['declared_cost'] = 10;
			}
		}
		
		# Не собирать посылку (только фулфилмент) По умолчанию: false
		# Boolean
		# необязательный
		$params['no_gather'] = false;
		
		if (! empty($data['no_gather'])) {
			$params['no_gather'] = true;
		}
		
		# Уникальный идентификатор заказа в вашем магазине
		# String		
		# необязательный		
		if (! empty($data['external_id'])) {
			$params['external_id'] = $data['external_id'];
		}
		
		# Данные об отправлении
		$params['departure'] = array();

		# ID способа доставки
		# Number
		if (! empty($data['shipping_method'])) {
			$params['departure']['shipping_method'] = (int)$data['shipping_method'];
		}

		# ID пункта самовывоза
		# String
		# необязательный
		if (! empty($data['delivery_point'])) {
			$params['departure']['delivery_point'] = (int)$data['delivery_point'];
		}
		
		# Рекомендованное время доставки Допустимые значения: "смотреть метод getDeliveryTime"
		# Number
		# необязательный
		if (! empty($data['delivery_time'])) {
			$params['departure']['delivery_time'] = (int)$data['delivery_time'];
		}
		
		if (! empty($data['cashless_payment'])) {
			$params['departure']['cashless_payment'] = true;
		}
		
		$params['departure']['comment'] = '';

		# Данные об адресе доставки
		$params['departure']['address'] = array();

		# Страна Допустимые значения: "RU"
		# String
		
		$params['departure']['address']['country'] = 'RU';
		
		if (! empty($data['country_code'])) {
			$params['departure']['address']['country'] = $data['country_code'];
		}
		else {
			if ($this->config->get('config_country_id')) {
				$this->load->model('localisation/country');
				$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
				if(!empty($country_info))$params['departure']['address']['country'] = $country_info['iso_code_2'];
			}
		}
		# Имя
		# String
		if (! empty($data['name'])) {
			$params['departure']['address']['name'] = $data['name'];
		}	
		
		# Фамилия
		# String
		if (! empty($data['surname'])) {
			$params['departure']['address']['surname'] = $data['surname'];
		}
		
		# Отчество
		# String
		# необязательный
		if (! empty($data['patronymic'])) {
			$params['departure']['address']['patronymic'] = $data['patronymic'];
		}

		# Адрес электронной почты
		# String
		if (! empty($data['email'])) {
			$params['departure']['address']['email'] = $data['email'];
		}

		# Номер телефона в международном формате (+7…)
		# String
		if (! empty($data['phone'])) {
			$params['departure']['address']['phone'] = $data['phone'];
		}

		# Почтовый индекс
		# String
		if (! empty($data['postal_code'])) {
			$params['departure']['address']['postal_code'] = $data['postal_code'];
		}

		# Область. Можно оставить пустой, если передан kladr_id.
		# String
		# необязательный
		if (! empty($data['administrative_area'])) {
			$params['departure']['address']['administrative_area'] = $data['administrative_area'];
		}

		# Населенный пункт. Можно оставить пустым, если передан kladr_id.
		# String
		# необязательный
		if (! empty($data['settlement'])) {
			$params['departure']['address']['settlement'] = $data['settlement'];
		}

		# Улица
		# String
		if (! empty($data['street'])) {
			$params['departure']['address']['address_line_1'] = $data['street'];
		}

		# Дом
		# String
		if (! empty($data['house'])) {
			$params['departure']['address']['house'] = $data['house'];
		}

		# Квартира
		# String
		if (! empty($data['apartment'])) {
			$params['departure']['address']['apartment'] = $data['apartment'];
		}

		# Код КЛАДР населенного пункта, можно получить из справочника населенных пунктов. При отсутствии будет определен автоматически.
		# String
		# необязательный
		if (! empty($data['kladr_id'])) {
			$params['departure']['address']['kladr_id'] = $data['kladr_id'];
		}
		
		# Cписок продуктов 
		# Array
		$this->load->language('shipping/shiptor');
		$costProduct = 0;

		if (! empty($data['products'])) {
			$params['products'] = array();
			
			foreach ($data['products'] as $product) {
				$costProduct += $product['count']*$product['price'];
				$params['products'][] = array(
					'shopArticle' => $product['shopArticle'],
					'count' 	  => $product['count'],
					'price' 	  => $product['price']
				);
			}
		}

		if (0 != $params['cod']) {
			$params['services'][] = array(
				'shopArticle'	=> mb_substr(HTTPS_CATALOG, 0, 64), // артикул услуги = url сайта (к этому моменту уже создана в ЛК шиптора)
				'price'		=> $params['cod']  - $costProduct,
				'count'		=> 1,
			);
		}
		
		return $this->request('editPackage', $params);
	}
	
	#Подтверждение отгрузки
	public function confirmShipment($shipment_id) {
		$params = array();

		if (isset($shipment_id)) {
			$params['id'] = $shipment_id;
		}

		return $this->request('confirmShipment', $params);
	}

	#Добавление услуги
	public function addService($data = array()) {
		$params = array();

		if (isset($data['name'])) {
			$params['name'] = $data['name'];
		}

		if (isset($data['shopArticle'])) {
			$params['shopArticle'] = $data['shopArticle'];
		}

		if (isset($data['price'])) {
			$params['price'] = $data['price'];
		}


		return $this->request('addService', $params);
	}

	
	# Проверка услуги (по названию)
	
	public function checkServiceByName($name) {
		$services = $this->getServices();

		foreach ($services['services'] as $service) {
			if ($name == $service['name']) return true;
		}

		return false;
	}

	
	# Проверка услуги (по артикулу)
	
	public function checkServiceByArticle($article) {
		$services = $this->getServices();

		foreach ($services['services'] as $service) {
			if ($article == $service['shop_article']) return true;
		}

		return false;
	}

	
	#Получение услуг
	public function getServices($data = array()) {
		$params = array();

		if (isset($data['page'])) {
			$params['page'] = $data['page'];
		}

		if (isset($data['per_page'])) {
			$params['per_page'] = $data['per_page'];
		}


		return $this->request('getServices', $params);
	}

	#Удаление услуги
	public function deleteService($data = array()) {
		$params = array();

		if (isset($data['shopArticle'])) {
			$params['shopArticle'] = $data['shopArticle'];
		}

		return $this->request('deleteService', $params);
	}
	
	# Shipping - addPickup — Оформление забора груза
	public function addPickup($data = array()) { 
		$params = array();

		# Number
		if (! empty($data['warehouse_id'])) {
			$params['warehouse_id'] = $data['warehouse_id'];
		}
		
		# Дата
		# String
		if (! empty($data['date'])) {
			$params['date'] = $data['date'];
		}

		# Array
		if (! empty($data['packages'])) {
			$params['packages'] = $data['packages'];
		}
		
		# Комментарий
		# String
		if (! empty($data['comment'])) {
			$params['comment'] = $data['comment'];
		} else {
			$params['comment'] = '';
		}
		
		return $this->request('addPickup', $params);
	}

	# Shipping - addProduct — Добавление товара
	public function addProduct($data = array()) {
		$params = array();
	
		# Название товара
		# String
		if (! empty($data['name'])) {
			$params['name'] = $data['name'];
		}
		
		# SKU
		# String
		# Необязательный
		if (! empty($data['sku'])) {
			$params['sku'] = $data['sku'];
		}
		
		# Артикул на товаре
		# String
		# Необязательный
		if (! empty($data['article'])) {
			$params['article'] = $data['article'];
		}

		# Артикул магазина
		# String
		# Необязательный
		if (! empty($data['shopArticle'])) {
			$params['shopArticle'] = $data['shopArticle'];
		}

		# Длина (см)
		# Number
		# Необязательный
		if (isset($data['length']) && 0 != (float)$data['length']) {
			$params['length'] = (float)$data['length'];
		}
		
		# Ширина (см)
		# Number
		# Необязательный
		if (isset($data['width']) && 0 != (float)$data['width']) {
			$params['width'] = (float)$data['width'];
		}
		
		# Высота (см)
		# Number
		# Необязательный
		if (isset($data['height']) && 0 != (float)$data['height']) {
			$params['height'] = (float)$data['height'];
		}
		
		# Вес (кг)
		# Number
		# Необязательный
		if (isset($data['weight']) && 0 != (float)$data['weight']) {
			$params['weight'] = (float)$data['weight'];
		}
		
		# Оптовая цена
		# Number
		# Необязательный
		if (isset($data['price']) && 0 != (float)$data['price']) {
			$params['price'] = (float)$data['price'];
		}
		
		# Розничная цена
		# Number
		# Необязательный
		if (! empty($data['price'])) {
			$params['retailPrice'] = (float)$data['price'];
		}
		
		# Хрупкий. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['fragile'])) {
			$params['fragile'] = true;
		}
		
		# Опасный. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['danger'])) {
			$params['danger'] = true;
		}
		
		# Скоропортящийся. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['perishable'])) {
			$params['perishable'] = true;
		}

		# Необходима упаковка. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['needBox'])) {
			$params['needBox'] = true;
		}

		return $this->request('addProduct', $params);
	}
	
	# Shipping - editProduct — Редактирование товара
	public function editProduct($data = array()) { 
		$params = array();
	
		# Название товара
		# String
		if (! empty($data['name'])) {
			$params['name'] = $data['name'];
		}
		
		# SKU
		# String
		# Необязательный
		if (! empty($data['sku'])) {
			$params['sku'] = $data['sku'];
		}

		# Артикул магазина
		# String
		# Необязательный
		if (! empty($data['shopArticle'])) {
			$params['shopArticle'] = $data['shopArticle'];
		}

		# Длина (см)
		# Number
		# Необязательный
		if (isset($data['length']) && 0 != (float)$data['length']) {
			$params['length'] = (float)$data['length'];
		}
		
		# Ширина (см)
		# Number
		# Необязательный
		if (isset($data['width']) && 0 != (float)$data['width']) {
			$params['width'] = (float)$data['width'];
		}
		
		# Высота (см)
		# Number
		# Необязательный
		if (isset($data['height']) && 0 != (float)$data['height']) {
			$params['height'] = (float)$data['height'];
		}
		
		# Вес (кг)
		# Number
		# Необязательный
		if (isset($data['weight']) && 0 != (float)$data['weight']) {
			$params['weight'] = (float)$data['weight'];
		}
		
		# Розничная цена
		# Number
		# Необязательный
		if (! empty($data['price'])) {
			$params['retailPrice'] = (float)$data['price'];
		}
		
		# Хрупкий. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['fragile'])) {
			$params['fragile'] = true;
		}
		
		# Опасный. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['danger'])) {
			$params['danger'] = true;
		}
		
		# Скоропортящийся. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['perishable'])) {
			$params['perishable'] = true;
		}

		# Необходима упаковка. По умолчанию: false
		# Boolean
		# Необязательный
		if (! empty($data['needBox'])) {
			$params['needBox'] = true;
		}

		return $this->request('editProduct', $params);
	}
	
	# Shipping - addShipment — Добавление поставки
	public function addShipment($data = array()) {
		$params = array();
		
		# Артикул товара в магазине
		# String
		if (! empty($data['shopArticle'])) {
			$params['shopArticle'] = $data['shopArticle'];
		}
		
		# Колличество товара в поставке
		# Number
		if (! empty($data['awaitingCount'])) {
			$params['awaitingCount'] = $data['awaitingCount'];
		}
		
		return $this->request('addShipment', $params);
	}
	
	# Shipping - addWarehouse — Добавление нового склада
	public function addWarehouse($data = array()) {
		$params = array();
		
		# E-mail ( mail@.mail.ru )
		# String
		if (! empty($data['name'])) {
			$params['name'] = $data['name'];
		}
		
		# Адрес ( 394000, Россия, Московаская обл, г Москва, ул Димитрова, 3, 37 )
		# String
		if (! empty($data['address'])) {
			$params['address'] = $data['address'];
		}		
		
		# Телефон ( +7 900 305-45-45 )
		# String
		if (! empty($data['phone'])) {
			$params['phone'] = $data['phone'];
		}		
		
		# Комментарий
		# String
		$params['comment'] = '';
		
		if (! empty($data['comment'])) {
			$params['comment'] = $data['comment'];
		}
		
		return $this->request('addWarehouse', $params);
	}
	
	# Shipping - calculateShipping — Расчет стоимости сквозной доставки
	final public function calculateShippingСutting($data = array()) {
		$params = array();
	
		# Длина, см
		# Number
		$params['length'] = 1;
		
		if (isset($data['length']) && 0 != (float)$data['length']) {
			$params['length'] = (float)$data['length'];
		} elseif ($this->config->get('shipping_shiptor_default_length')) {
			$params['length'] = (float)$this->config->get('shipping_shiptor_default_length');
		}

		# Ширина, см
		# Number
		$params['width'] = 1;
		
		if (isset($data['width']) && 0 != (float)$data['width']) {
			$params['width'] = (float)$data['width'];
		} elseif ($this->config->get('shipping_shiptor_default_width')) {
			$params['width'] = (float)$this->config->get('shipping_shiptor_default_width');
		}
			
		# Высота, см
		# Number
		
		$params['height'] = 1;
		
		if (isset($data['height']) && 0 != (float)$data['height']) {
			$params['height'] = (float)$data['height'];
		} elseif ($this->config->get('shipping_shiptor_default_height')) {
			$params['height'] = (float)$this->config->get('shipping_shiptor_default_height');
		}
  		
		# Вес, кг
		# Number
		$params['weight'] = 1;
		
		if (isset($data['weight']) && 0 != (float)$data['weight']) {
			$params['weight'] = (float)$data['weight'];
		}

		# Сумма наложенного платежа, руб. Если отсутствует - передавайте 0
		# Number
		
		$params['cod'] = 0;
		
		if (! empty($data['cod'])) {
			$params['cod'] = $data['cod'];
		}
	
		# Объявленная ценность, руб. Если отсутствует - передавайте 0.
		# Если есть наложенный платеж, то он должен быть равен объявленной ценности.
		# Number
		
		$params['declared_cost'] = 0;
		
		if (isset($data['declared_cost']) && 0 != (float)$data['declared_cost']) {
			$params['declared_cost'] = (float)$data['declared_cost'];
		}

		# Код страны для расчета. Допустимые значения: "RU", "KZ"
		# String
		# необязательный
		
		$params['country_code'] = 'RU';
		
		if (! empty($data['country_code'])) {
			$params['country_code'] = $data['country_code'];
		}
		else {
			if ($this->config->get('config_country_id')) {
				$this->load->model('localisation/country');
				$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
				if(!empty($country_info))$params['country_code'] = $country_info['iso_code_2'];
			}
		}

		# Строковый идентификатор курьерской службы в системе
		# Допустимые значения: "shiptor", "b2c", "boxberry", "dpd", "iml", "russian-post", "pickpoint", "cdek", "shiptor-one-day", "spsr", "shiptor-oversize"
		# String
		# необязательный
		if (! empty($data['courier'])) {
			$params['courier'] = $data['courier'];
		}
		
		# Идентификатор КЛАДР населенного пункта отправителя
		# String
		# необязательный
		if (!empty($this->config->get('shipping_shiptor_city_delivery'))) {
			$params['kladr_id_from'] = $this->config->get('shipping_shiptor_city_delivery_value');
		}

		# Идентификатор КЛАДР населенного пункта получателя
		# String
		if (! empty($data['kladr_id'])) {
			$params['kladr_id'] = $data['kladr_id'];
		}
		
		if($this->config->get('shipping_shiptor_logger'))$params['verbose'] = 1;
		$cache = 'shiptor.shipping.calculateShipping.' . md5(implode('', $params));
		if (! $calculate = $this->cache->get($cache)) {
			$calculate = $this->request('calculateShipping', $params);
			$this->cache->set($cache, $calculate);
		}
		if(isset($calculate['methods'])){
			return $calculate;
		}else{
			$calculate = array();
			return $calculate;
		}
	}
	
	# Shipping - calculateShipping — Расчет стоимости доставки
	final public function calculateShipping($data = array()) {
		$params = array();
	
		# Длина, см
		# Number
		$params['length'] = 1;
		
		if (isset($data['length']) && 0 != (float)$data['length']) {
			$params['length'] = (float)$data['length'];
		} elseif ($this->config->get('shipping_shiptor_default_length')) {
			$params['length'] = (float)$this->config->get('shipping_shiptor_default_length');
		}

		# Ширина, см
		# Number
		$params['width'] = 1;
		
		if (isset($data['width']) && 0 != (float)$data['width']) {
			$params['width'] = (float)$data['width'];
		} elseif ($this->config->get('shipping_shiptor_default_width')) {
			$params['width'] = (float)$this->config->get('shipping_shiptor_default_width');
		}
			
		# Высота, см
		# Number
		
		$params['height'] = 1;
		
		if (isset($data['height']) && 0 != (float)$data['height']) {
			$params['height'] = (float)$data['height'];
		} elseif ($this->config->get('shipping_shiptor_default_height')) {
			$params['height'] = (float)$this->config->get('shipping_shiptor_default_height');
		}
  		
		# Вес, кг
		# Number
		$params['weight'] = 1;
		
		if (isset($data['weight']) && 0 != (float)$data['weight']) {
			$params['weight'] = (float)$data['weight'];
		}

		# Сумма наложенного платежа, руб. Если отсутствует - передавайте 0
		# Number
		
		$params['cod'] = 0;
		
		if (! empty($data['cod'])) {
			$params['cod'] = $data['cod'];
		}
	
		# Объявленная ценность, руб. Если отсутствует - передавайте 0.
		# Если есть наложенный платеж, то он должен быть равен объявленной ценности.
		# Number
		
		$params['declared_cost'] = 0;
		
		if (isset($data['declared_cost']) && 0 != (float)$data['declared_cost']) {
			$params['declared_cost'] = (float)$data['declared_cost'];
		}

		# Код страны для расчета. Допустимые значения: "RU", "KZ"
		# String
		# необязательный
		
		$params['country_code'] = 'RU';
		
		if (! empty($data['country_code'])) {
			$params['country_code'] = $data['country_code'];
		}
		else {
			if ($this->config->get('config_country_id')) {
				$this->load->model('localisation/country');
				$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
				if(!empty($country_info))$params['country_code'] = $country_info['iso_code_2'];
			}
		}

		# Строковый идентификатор курьерской службы в системе
		# Допустимые значения: "shiptor", "b2c", "boxberry", "dpd", "iml", "russian-post", "pickpoint", "cdek", "shiptor-one-day", "spsr", "shiptor-oversize"
		# String
		# необязательный
		if (! empty($data['courier'])) {
			$params['courier'] = $data['courier'];
		}
		
		# Идентификатор КЛАДР населенного пункта отправителя
		# String
		# необязательный
		if (! empty($data['kladr_id_from'])) {
			$params['kladr_id_from'] = $data['kladr_id_from'];
		}

		# Идентификатор КЛАДР населенного пункта получателя
		# String
		if (! empty($data['kladr_id'])) {
			$params['kladr_id'] = $data['kladr_id'];
		}
		
		if($this->config->get('shipping_shiptor_logger'))$params['verbose'] = 1;
		$cache = 'shiptor.shipping.calculateShipping.' . md5(implode('', $params));
		if (! $calculate = $this->cache->get($cache)) {
			$calculate = $this->request('calculateShipping', $params);
			$this->cache->set($cache, $calculate);
		}
		if(isset($calculate['methods'])){
			return $calculate;
		}else{
			$calculate = array();
			return $calculate;
		}
	}
	
	# Shipping - cancelPickUp — Отмена забора груза
	public function cancelPickUp($parcel_id) {
		$params = array();
		
		# Идентификационный номер посылки
		# Number
		if (! empty($parcel_id)) {
			$params['id'] = $parcel_id;
		}
		
		return $this->request('cancelPickUp', $params);
	}
	
	# Shipping - deleteProduct — Удаление товара
	public function deleteProduct($shop_article) {
		$params = array();
		
		# Артикул магазина
		# String
		if (! empty($shop_article)) {
			$params['shopArticle'] = $shop_article;
		}

		return $this->request('deleteProduct', $params);
	}
	
	# Shipping - getCountries — Получение списка стран и их кодов
	final public function getCountries() { 
		return $this->request('getCountries');
	}
	
	# Shipping - getDeliveryPoints — Получения списка ПВЗ
	final public function getDeliveryPoints($data = array()) {
		$params = array();

		# Код КЛАДР населенного пункта, можно получить из справочника населенных пунктов
		# String
		if (! empty($data['kladr_id'])) {
			$params['kladr_id'] = $data['kladr_id'];
		}
		
		# Возможность самопривоза груза
		# Number
		if (! empty($data['self_pick_up'])) {
			$params['self_pick_up'] = $data['self_pick_up'];
		}

		# Названия службы доставки
		# String
		if (! empty($data['courier'])) {
			$params['courier'] = $data['courier'];
		}

		# Идентификатор метода доставки
		# Number
		if (! empty($data['shipping_method'])) {
			$params['shipping_method'] = $data['shipping_method'];
		}

		# ПВЗ с наложенным платежом
		# Boolean
		# необязательный
		if (! empty($data['cod'])) {
			$params['cod'] = $data['cod'];
		}

		# ПВЗ принимает безналичный расчёт
		# Boolean
		# необязательный
		if (! empty($data['card'])) {
			$params['card'] = $data['card'];
		}

		# Ограничения по параметрам посылки
		# необязательный
		$params['limits'] = array();
	
		# Длина, см
		# Number
		$params['limits']['length'] = 1;
		
		if (isset($data['length']) && 0 != (float)$data['length']) {
			$params['limits']['length'] = (float)$data['length'];
		} elseif ($this->config->get('shipping_shiptor_default_length')) {
			$params['limits']['length'] = (float)$this->config->get('shipping_shiptor_default_length');
		}

		# Ширина, см
		# Number
		$params['limits']['width'] = 1;
		
		if (isset($data['width']) && 0 != (float)$data['width']) {
			$params['limits']['width'] = (float)$data['width'];
		} elseif ($this->config->get('shipping_shiptor_default_width')) {
			$params['limits']['width'] = (float)$this->config->get('shipping_shiptor_default_width');
		}
			
		# Высота, см
		# Number
		
		$params['limits']['height'] = 1;
		
		if (isset($data['height']) && 0 != (float)$data['height']) {
			$params['limits']['height'] = (float)$data['height'];
		} elseif ($this->config->get('shipping_shiptor_default_height')) {
			$params['limits']['height'] = (float)$this->config->get('shipping_shiptor_default_height');
		}
  		
		# Вес, кг
		# Number
		$params['limits']['weight'] = 1;
		
		if (isset($data['weight']) && 0 != (float)$data['weight']) {
			$params['limits']['weight'] = (float)$data['weight'];
		}
		
		return $this->request('getDeliveryPoints', $params);
	}
	
	# Shipping - getDeliveryTime — Получение списка интервалов доставки
	final public function getDeliveryTime() {	
		$cache = 'shiptor.shipping.getDeliveryTime';
		if (! $deliveryTime = $this->cache->get($cache)) {
			$deliveryTime = $this->request('getDeliveryTime');
			$this->cache->set($cache, $deliveryTime);
		}
		return $deliveryTime;
	}
	
	# Shipping - getDaysOff  — Получение списка нерабочих дней
	final public function getDaysOff() {
		$nextDay = date("Y-m-d",strtotime("+1 days"));
		$params = array('from'=>$nextDay,
						'till'=>$nextDay);
		$cache = 'shiptor.shipping.getDaysOff.'.$nextDay;
		$cacheResult = $this->cache->get($cache);
		if(!empty($cacheResult))$daysOff = unserialize($cacheResult);
		if(!isset($daysOff['result'])){
			$daysOff = array('result'=>$this->request('getDaysOff',$params,'public'));
			$this->cache->set($cache, serialize($daysOff));
		}
		if(!empty($daysOff['result']) && $daysOff['result'][0]==$nextDay) return false;
		else return true;
	}
	
	# Shipping - getDaysOff  — Получение списка нерабочих дней для отгрузки
	final public function getDaysOffShipment($data) {
		 
		$nextDay = date("Y-m-d",$data['date_shipment']);
		$nextDay_from = date("Y-m-d",$data['date_shipment_from']);
		
		$params['from'] = $nextDay_from;
		$params['till'] = $nextDay;
		
		return $this->request('getDaysOff',$params,'public');
		//else return true;
	}
	
	# Shipping - getPackage — Получение статуса посылки
	public function getPackage($parcel_id) {
		$params = array();
		
		# Идентификационный номер посылки
		# Number
		if (! empty($parcel_id)) {
			$params['id'] = $parcel_id;
		}
		
		return $this->request('getPackage', $params);
	}
	
	# Shipping - getPackages — Получение списка посылок
	final public function getPackages($data = array()) {		
		$params = array();
		
		# Страница
		# Number
		
		$params['page'] = 1;
		
		if (! empty($data['page'])) {
			$params['page'] = (int)$data['page'];
		}
		
		# Количество на странице
		# Number
		
		$params['per_page'] = 10;
		
		if (! empty($data['per_page'])) {
			$params['per_page'] = (int)$data['per_page'];
		}
		
		if (! empty($data['delivered'])) {
			$params['delivered'] = 1;
		}
		
		if (! empty($data['returned'])) {
			$params['returned'] = 1;
		}

		return $this->request('getPackages', $params);
	}
	
	# Shipping - getPackagesCount
	final public function getPackagesCount($data = array()) {		
		$params = array();
		
		if (! empty($data['delivered'])) {
			$params['delivered'] = 1;
		}
		

		if (! empty($data['returned'])) {
			$params['returned'] = 1;
		}
		
		return $this->request('getPackagesCount', $params);	
	}
	
	# Shipping - getPickup — Получение информации о заборе груза
	public function getPickup($parcel_id) {
		$params = array();
		
		# Идентификационный номер посылки
		# Number
		if (! empty($parcel_id)) {
			$params['id'] = $parcel_id;
		}
		
		return $this->request('getPickup', $params);
	}
	
	# Shipping - getProducts — Получение товаров
	public function getProducts($data = array()) {
		$params = array();
		
		# Страница
		# Number
		
		$params['page'] = 1;

		if (! empty($data['page'])) {
			$params['page'] = $data['page'];
		}
		
		# Количество на странице
		# Number
		
		$params['per_page'] = 10;
		
		if (! empty($data['per_page'])) {
			$params['per_page'] = $data['per_page'];
		}
		
		if (! empty($data['shopArticle'])) {
			$params['shopArticle'] = $data['shopArticle'];
		}
		
		return $this->request('getProducts', $params);
	}
	
	# Shipping - getProfile — Получение профиля пользователя
	final public function getProfile() {
		return $this->request('getProfile');
	}
	
	# Shipping - getSettlements — Получение справочника населенных пунктов
	final public function getSettlements($data = array()) {
		$params = array();
  		
		# Сколько выводить на страницу
		# Number		
		$params['per_page'] = 10;
		
		if (! empty($data['per_page'])) {
			$params['per_page'] = $data['per_page'];
		}

		# Какая страница
		# Number		
		$params['page'] = 1;
		
		if (! empty($data['page'])) {
			$params['page'] = $data['page'];
		}

		# Тип
		# Array
		# необязательный  	
		$params['types'] = array();
		
		if (! empty($data['types'])) {
			$params['types'] = $data['types'];
		}
		
		# Уровень
		# Number		
		$params['level'] = '';
		
		if (! empty($data['level'])) {
			$params['level'] = $data['level'];
		}

		# Кладр родителя
		# String
		$params['parent'] = '';
		
		if (! empty($data['parent'])) {
			$params['parent'] = $data['parent'];
		}

		# Индетификатор страны. Допустимые значения: "RU", "KZ"
		# String
		$params['country_code'] = 'RU';
		
		$this->load->model('localisation/country');
		
		if (! empty($data['country_code'])) {
			$country_info = $this->model_localisation_country->getCountry($data['country_code']);
		}
		else {
			if ($this->config->get('config_country_id')) {
				$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
			}
		}
		if(!empty($country_info))$params['country_code'] = $country_info['iso_code_2'];
		$cache = 'shiptor.shipping.getSettlements.' . md5(implode('', $params));
		if (! $settlements = $this->cache->get($cache)) {
			$settlements = $this->request('getSettlements', $params);
			$this->cache->set($cache, $settlements);
		}
		return $this->request('getSettlements', $params);
	}

	# Shipping - getShippingMethods — Получение справочника способов доставки
	final public function getShippingMethods() {
		return $this->request('getShippingMethods');
	}

	# Shipping - getWarehouses — Получение списка складов
	final public function getWarehouses() {
		return $this->request('getWarehouses');
	}

	# Shipping - recoverProduct — Восстановление товара
	public function recoverProduct($article) { 
		$params = array();

		# Артикул магазина	
		# String
		if (! empty($article)) {
			$params['shopArticle'] = $article;
		}

		return $this->request('recoverProduct', $params);
	}
	
	# Shipping - removeWarehouse — Удаление склада
	public function removeWarehouse($stockId) {
		$params = array();

		if (! empty($stockId)) {
			$params['id'] = $stockId;
		}
	
		return $this->request('removeWarehouse', $params);
	}
	
	# Shipping - removePackage — Удаление поссылки
	public function removePackage($shiptorId) {
		$params = array();

		if (! empty($shiptorId)) {
			$params['id'] = $shiptorId;
		}
	
		return $this->request('removePackage', $params);
	}
	
	# Shipping - revertWarehouse — Востановление склада из удалённых
	public function revertWarehouse($stockId) {
		$params = array();

		if (! empty($stockId)) {
			$params['id'] = $stockId;
		}

		return $this->request('revertWarehouse');
	}

	# Shipping - suggestSettlement — Получение населенного пункта по части названия
	final public function suggestSettlement($data = array()) {
		$params = array();

		# Поисковый запрос
		# String
		if (! empty($data['query'])) {
			$params['query'] = trim($data['query']);
		}
		
		if (! empty($data['parent'])) {
			$params['parent'] = $data['parent'];
		}

		# Страна Допустимые значения: "RU", "KZ"
		# String
		$params['country_code'] = 'RU';
		
		if (! empty($data['country_code'])) {
			$params['country_code'] = $data['country_code'];
		}
		else {
			if ($this->config->get('config_country_id')) {
				$this->load->model('localisation/country');
				$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
				if(!empty($country_info))$params['country_code'] = $country_info['iso_code_2'];
			}
		}
		
		$cache = 'shiptor.shipping.suggestSettlement.' . md5(implode('', $params));
		if (! $settlements = $this->cache->get($cache)) {
			$settlements = $this->request('suggestSettlement', $params);
			$this->cache->set($cache, $settlements);
		}
		return $settlements;
	}
	
	# Shipping - updateProfile — Получение профиля пользователя
	public function updateProfile() {
		return $this->request('updateProfile');
	}
	
	final private function request($method = '', $params = array(), $type = 'shipping') {
		$response_data = array();
		$version = "oc-3.0";

		$data = array('id' => 'JsonRpcClient.js', 'jsonrpc' => '2.0', 'method' => $method, 'params' => $params);

		$query = json_encode($data);

		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, 'https://api.shiptor.ru/'.$type.'/v1');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Integration-Name: OpenCart','Integration-Version: ' . $version, 'Content-Type: application/json', 'x-authorization-token: ' . trim($this->config->get('shipping_shiptor_authorization'))));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		
		$response = curl_exec($ch);
		
		$header_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($this->config->get('shipping_shiptor_logger')) {
			$this->log->write('|||||||||| START DEBUG LOGGER ||||||||||');			
			$this->log->write('Отправленные данные - ' . print_r($data, true));
			$this->log->write('Код ответа - ' . $header_code);
			$this->log->write('Полученный ответ ответа - ' . print_r(json_decode($response, true), true));
			$this->log->write('|||||||||| END DEBUG LOGGER ||||||||||');
		}

		if ($header_code == 200) {
			$response = json_decode($response, true);

			if (! isset($response['error'])) {		
				$response_data = $response['result'];
			}
		}

		return $response_data;		
	}
  }