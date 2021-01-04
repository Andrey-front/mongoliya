<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ModelExtensionShippingShiptor extends Model {
	public function __construct($registry) {
		parent::__construct($registry);
		$registry->set('shiptor', new Shiptor($registry));
	}

	public function install(){
		$this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'shipping_shiptor_address`');
		$this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'shipping_shiptor_address` (`address_id` int(11) NOT NULL AUTO_INCREMENT, `customer_id` int(11) NOT NULL, `kladr_id` varchar(25) NOT NULL, PRIMARY KEY (`address_id`), KEY `customer_id` (`customer_id`), KEY `kladr_id` (`kladr_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8');
		
		$this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'shipping_shiptor_couriers`');
		$this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'shipping_shiptor_couriers` ( `id` int(11) NOT NULL, `name` varchar(30) NOT NULL, `category` varchar(30) NOT NULL, `courier` varchar(30) NOT NULL, PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8');
			
		$this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'shipping_shiptor_сountries`');
		$this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'shipping_shiptor_сountries` ( `id` int(11) NOT NULL AUTO_INCREMENT, `code` varchar(5) NOT NULL, `name` varchar(30) NOT NULL, PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8');
		
		$this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'shipping_shiptor_regions`');
		$this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'shipping_shiptor_regions` ( `id` varchar(11) NOT NULL, `zone_id` int(11) NOT NULL, `name` varchar(100) NOT NULL, PRIMARY KEY (`id`,`zone_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8');
		
		$this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'shipping_shiptor_orders` ( `order_id` int(11) NOT NULL, `shiptor_id` int(11) NOT NULL, `shipment_id` int(11) NOT NULL, `date_shipment` DATETIME NOT NULL, `delivery_point` int(11) NOT NULL, `patronymic` varchar(100) NOT NULL, `address` varchar(150) NOT NULL, `street` varchar(100) NOT NULL, `house` varchar(10) NOT NULL, `apartment` varchar(10) NOT NULL, `shipping_method` int(11) NOT NULL, `kladr_id` varchar(25) NOT NULL, `time` tinyint(1) NOT NULL, `weight` decimal(15,2) NOT NULL, PRIMARY KEY (`order_id`), KEY `shiptor_id` (`shiptor_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		$regions = array(
			array('id' => 1, 'zone_id' => 2760, 'name' => 'Адыгея Республика'),
			array('id' => 2, 'zone_id' => 2794, 'name' => 'Башкортостан Республика'),
			array('id' => 3, 'zone_id' => 2796, 'name' => 'Бурятия Республика'),
			array('id' => 4, 'zone_id' => 2738, 'name' => 'Алтай Республика'),
			array('id' => 5, 'zone_id' => 2759, 'name' => 'Дагестан Республика'),
			array('id' => 6, 'zone_id' => 2765, 'name' => 'Ингушетия Республика'),
			array('id' => 7, 'zone_id' => 2763, 'name' => 'Кабардино-Балкарская Республика'),
			array('id' => 8, 'zone_id' => 2736, 'name' => 'Калмыкия Республика'),
			array('id' => 9, 'zone_id' => 2733, 'name' => 'Карачаево-Черкесская Республика'),
			array('id' => 10, 'zone_id' => 2776, 'name' => 'Карелия Республика'),
			array('id' => 11, 'zone_id' => 2787, 'name' => 'Коми Республика'),
			array('id' => 12, 'zone_id' => 2808, 'name' => 'Марий Эл Республика'),
			array('id' => 13, 'zone_id' => 2782, 'name' => 'Мордовия Республика'),
			array('id' => 14, 'zone_id' => 2805, 'name' => 'Саха Республика (Якутия)'),
			array('id' => 15, 'zone_id' => 2798, 'name' => 'Северная Осетия - Алания Республика'),
			array('id' => 16, 'zone_id' => 2746, 'name' => 'Татарстан Республика'),
			array('id' => 17, 'zone_id' => 2756, 'name' => 'Тыва Республика'),
			array('id' => 18, 'zone_id' => 2742, 'name' => 'Удмуртская Республика'),
			array('id' => 19, 'zone_id' => 2721, 'name' => 'Хакасия Республика'),
			array('id' => 20, 'zone_id' => 2739, 'name' => 'Чеченская Республика'),
			array('id' => 21, 'zone_id' => 2731, 'name' => 'Чувашская Республика - Чувашия'),
			array('id' => 22, 'zone_id' => 2726, 'name' => 'Алтайский Край'),
			array('id' => 23, 'zone_id' => 2751, 'name' => 'Краснодарский Край'),
			array('id' => 24, 'zone_id' => 2752, 'name' => 'Красноярский Край'),
			array('id' => 25, 'zone_id' => 2800, 'name' => 'Приморский Край'),
			array('id' => 26, 'zone_id' => 2786, 'name' => 'Ставропольский Край'),
			array('id' => 27, 'zone_id' => 2748, 'name' => 'Хабаровский Край'),
			array('id' => 28, 'zone_id' => 2729, 'name' => 'Амурская Область'),
			array('id' => 29, 'zone_id' => 2724, 'name' => 'Архангельская Область'),
			array('id' => 30, 'zone_id' => 2725, 'name' => 'Астраханская Область'),
			array('id' => 31, 'zone_id' => 2727, 'name' => 'Белгородская Область'),
			array('id' => 32, 'zone_id' => 2730, 'name' => 'Брянская Область'),
			array('id' => 33, 'zone_id' => 2799, 'name' => 'Владимирская Область'),
			array('id' => 34, 'zone_id' => 2801, 'name' => 'Волгоградская Область'),
			array('id' => 35, 'zone_id' => 2802, 'name' => 'Вологодская Область'),
			array('id' => 36, 'zone_id' => 2803, 'name' => 'Воронежская Область'),
			array('id' => 37, 'zone_id' => 2741, 'name' => 'Ивановская Область'),
			array('id' => 38, 'zone_id' => 2740, 'name' => 'Иркутская Область'),
			array('id' => 39, 'zone_id' => 2743, 'name' => 'Калининградская Область'),
			array('id' => 40, 'zone_id' => 2744, 'name' => 'Калужская Область'),
			array('id' => 41, 'zone_id' => 2775, 'name' => 'Камчатский Край'),
			array('id' => 42, 'zone_id' => 2747, 'name' => 'Кемеровская Область'),
			array('id' => 43, 'zone_id' => 2804, 'name' => 'Кировская Область'),
			array('id' => 44, 'zone_id' => 2750, 'name' => 'Костромская Область'),
			array('id' => 45, 'zone_id' => 2754, 'name' => 'Курганская Область'),
			array('id' => 46, 'zone_id' => 2755, 'name' => 'Курская Область'),
			array('id' => 47, 'zone_id' => 2735, 'name' => 'Ленинградская Область'),
			array('id' => 48, 'zone_id' => 2757, 'name' => 'Липецкая Область'),
			array('id' => 49, 'zone_id' => 2758, 'name' => 'Магаданская Область'),
			array('id' => 50, 'zone_id' => 2722, 'name' => 'Московская Область'),
			array('id' => 51, 'zone_id' => 2762, 'name' => 'Мурманская Область'),
			array('id' => 52, 'zone_id' => 2766, 'name' => 'Нижегородская Область'),
			array('id' => 53, 'zone_id' => 2767, 'name' => 'Новгородская Область'),
			array('id' => 54, 'zone_id' => 2768, 'name' => 'Новосибирская Область'),
			array('id' => 55, 'zone_id' => 2769, 'name' => 'Омская Область'),
			array('id' => 56, 'zone_id' => 2771, 'name' => 'Оренбургская Область'),
			array('id' => 57, 'zone_id' => 2770, 'name' => 'Орловская Область'),
			array('id' => 58, 'zone_id' => 2773, 'name' => 'Пензенская Область'),
			array('id' => 59, 'zone_id' => 2774, 'name' => 'Пермский Край'),
			array('id' => 60, 'zone_id' => 2777, 'name' => 'Псковская Область'),
			array('id' => 61, 'zone_id' => 2778, 'name' => 'Ростовская Область'),
			array('id' => 62, 'zone_id' => 2779, 'name' => 'Рязанская Область'),
			array('id' => 63, 'zone_id' => 2781, 'name' => 'Самарская Область'),
			array('id' => 64, 'zone_id' => 2783, 'name' => 'Саратовская Область'),
			array('id' => 65, 'zone_id' => 2737, 'name' => 'Сахалинская Область'),
			array('id' => 66, 'zone_id' => 2807, 'name' => 'Свердловская Область'),
			array('id' => 67, 'zone_id' => 2784, 'name' => 'Смоленская Область'),
			array('id' => 68, 'zone_id' => 2788, 'name' => 'Тамбовская Область'),
			array('id' => 69, 'zone_id' => 2792, 'name' => 'Тверская Область'),
			array('id' => 70, 'zone_id' => 2789, 'name' => 'Томская Область'),
			array('id' => 71, 'zone_id' => 2790, 'name' => 'Тульская Область'),
			array('id' => 72, 'zone_id' => 2793, 'name' => 'Тюменская Область'),
			array('id' => 73, 'zone_id' => 2795, 'name' => 'Ульяновская Область'),
			array('id' => 74, 'zone_id' => 2732, 'name' => 'Челябинская Область'),
			array('id' => 75, 'zone_id' => 2734, 'name' => 'Забайкальский Край'),
			array('id' => 76, 'zone_id' => 2806, 'name' => 'Ярославская Область'),
			array('id' => 77, 'zone_id' => 2761, 'name' => 'Москва Город'),
			array('id' => 78, 'zone_id' => 2785, 'name' => 'Санкт-Петербург Город'),
			array('id' => 79, 'zone_id' => 2728, 'name' => 'Еврейская Автономная область'),
			array('id' => 83, 'zone_id' => 2764, 'name' => 'Ненецкий Автономный округ'),
			array('id' => 86, 'zone_id' => 2749, 'name' => 'Ханты-Мансийский Автономный округ - Югра Автономный округ'),
			array('id' => 87, 'zone_id' => 2723, 'name' => 'Чукотский Автономный округ'),
			array('id' => 89, 'zone_id' => 2780, 'name' => 'Ямало-Ненецкий Автономный округ'),
			array('id' => 80, 'zone_id' => 0, 'name' => 'Забайкальский край Агинский Бурятский Округ'),
			array('id' => 81, 'zone_id' => 0, 'name' => 'Коми-Пермяцкий Автономный округ'),
			array('id' => 82, 'zone_id' => 0, 'name' => 'Корякский Автономный округ'),
			array('id' => 84, 'zone_id' => 0, 'name' => 'Таймырский (Долгано-Ненецкий) Автономный округ'),
			array('id' => 85, 'zone_id' => 0, 'name' => 'Иркутская обл Усть-Ордынский Бурятский Округ'),
			array('id' => 88, 'zone_id' => 0, 'name' => 'Эвенкийский Автономный округ'),			
			array('id' => 91, 'zone_id' => 0, 'name' => 'Крым Республика'),
			array('id' => 92, 'zone_id' => 0, 'name' => 'Севастополь Город'),
			array('id' => 99, 'zone_id' => 0, 'name' => 'Байконур Город')
		);

		foreach ($regions as $region) {
			if ($region['zone_id']) {
				$this->db->query('INSERT INTO `' . DB_PREFIX . 'shipping_shiptor_regions` SET `id` = "' . $this->db->escape($region['id']) . '", `zone_id` = ' . (int)$region['zone_id'] . ', `name` = "' . $this->db->escape($region['name']) . '"');
			} else {
				$query = $this->db->query('SELECT `zone_id` FROM `' . DB_PREFIX . 'zone` WHERE `name` = "' . $this->db->escape($region['name']) . '" LIMIT 1');
				
				if (! isset($query->row['zone_id'])) {
					$this->db->query('INSERT INTO `' . DB_PREFIX . 'zone` SET `country_id` = 176, `name` = "' . $this->db->escape($region['name']) . '", `status` = 1');

					$zone_id = $this->db->getLastId();

					$this->db->query('INSERT INTO `' . DB_PREFIX . 'shipping_shiptor_regions` SET `id` = "' . $this->db->escape($region['id']) . '", `zone_id` = ' . (int)$zone_id . ', `name` = "' . $this->db->escape($region['name']) . '"');
				}
			}
		}
	}

	public function uninstall(){	
		$this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'shipping_shiptor_couriers`');
		$this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'shipping_shiptor_сountries`');
		$this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'shipping_shiptor_regions`');
	}
	
	public function showAndAlter(){
		$query = $this->db->query('show columns FROM ' . DB_PREFIX . 'shipping_shiptor_orders where `Field` = "shipment_id"');
		$query_date = $this->db->query('show columns FROM ' . DB_PREFIX . 'shipping_shiptor_orders where `Field` = "date_shipment"');
		
		if (!$query->num_rows) {
            $this->db->query('ALTER TABLE `' . DB_PREFIX . 'shipping_shiptor_orders` ADD `shipment_id` INT(11) NOT NULL');
        }
		
		if (!$query_date->num_rows) {
            $this->db->query('ALTER TABLE `' . DB_PREFIX . 'shipping_shiptor_orders` ADD `date_shipment` DATETIME NOT NULL');
        }
	}

	public function setShippingMethods($shipping_methods = array()){
		if ($shipping_methods) {
			$this->db->query('TRUNCATE TABLE `' . DB_PREFIX . 'shipping_shiptor_couriers`');

			foreach ($shipping_methods as $shipping_method) {
				$this->db->query('INSERT INTO `' . DB_PREFIX . 'shipping_shiptor_couriers` SET `id` = ' . (int)$shipping_method['id'] . ', `name` = "' . $this->db->escape($shipping_method['name']) . '", `category` = "' . $this->db->escape($shipping_method['category']) . '", `courier` = "' . $this->db->escape($shipping_method['courier']) . '"');
			}
		}
	}
	
	public function getShippingMethods(){
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_couriers` ORDER BY `name` ASC');

		return $query->rows;
	}
	
	public function getShippingMethod($shipping_method){
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_couriers` WHERE id = "' . $shipping_method . '" ORDER BY `name` ASC');

		return $query->row;
	}
	
	public function setCountries($сountries = array()){
		if ($сountries) {
			$this->db->query('TRUNCATE TABLE `' . DB_PREFIX . 'shipping_shiptor_сountries`');
			
			foreach ($сountries as $сountry) {
				$this->db->query('INSERT INTO `' . DB_PREFIX . 'shipping_shiptor_сountries` SET `code` = "' . $this->db->escape($сountry['code']) . '", `name` = "' . $this->db->escape($сountry['name']) . '"');
			}			
		}
	}
	
	public function getCountries(){
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_сountries` ORDER BY `code` ASC');

		return $query->rows;
	}

	public function getRegions($data = array()){
		$sql = 'SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_regions`';

		if (!empty($data['filter_name'])) {
			$sql .= ' WHERE LCASE(name) LIKE "%' . $this->db->escape(utf8_strtolower($data['filter_name'])) . '%"';
		}

		$sql .= ' ORDER BY `name` ASC';

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}	
	
	public function getRegionId($id){
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_regions` WHERE `id` = "' . $this->db->escape($id) . '" ORDER BY `id` ASC');

		return $query->row;
	}

	public function getOrder($order_id) {
		return $this->db->query('SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`shipping_address_1`,
								o.`total`, o.`shipping_code`, so.`shiptor_id`, so.`delivery_point`, so.`street`, so.`house`, so.`apartment`,
								so.`shipping_method`, so.`kladr_id`, so.`time`, so.`weight`, o.`shipping_zone`, o.`shipping_city`, o.`shipping_postcode`,
								(SELECT `iso_code_2` FROM `' . DB_PREFIX . 'country` WHERE `country_id`=o.shipping_country_id) AS country
								FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
								LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
								WHERE so.`order_id` = ' . (int)$order_id)->row;
	}
	
	public function getDateOrders($valid_period) {
		$sql = 'SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`payment_method`, o.`shipping_address_1`,
		o.`total`, o.`date_added`, so.`shiptor_id`, so.`delivery_point`, so.`address`, so.`street`, so.`house`, so.`apartment`, so.`shipping_method`,
		so.`kladr_id`, so.`time`, so.`weight`, o.`order_status_id`, os.`name` AS status, o.`shipping_city`
		FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
		LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
		LEFT JOIN `' . DB_PREFIX . 'order_status` os ON (o.`order_status_id` = os.`order_status_id`)
		WHERE 1 = 1 AND o.`order_status_id`!=0 ';
		
		$sql .= ' GROUP BY o.`order_id` ORDER BY o.`order_id` DESC, o.`date_added` DESC';
		
		$query = $this->db->query($sql);
		
		$order_periods = array();
		
		foreach($query->rows as $row){
			$shipping_method = $this->getShippingMethod($row['shipping_method']);
			if(!empty($shipping_method)){
				$period = $valid_period + strtotime($row['date_added']);
				if($period > time() 
				&& $row['order_status_id'] == $this->config->get('shipping_shiptor_order_status_for_api') 
				&& $row['shiptor_id'] == 0
				&& (stristr($shipping_method['category'],'-to-') == false)){
					$order_periods[] = array(
						'order_id'				=> $row['order_id'],
						'firstname'				=> $row['firstname'],
						'lastname'				=> $row['lastname'],
						'email'					=> $row['email'],
						'telephone'				=> $row['telephone'],
						'payment_code'			=> $row['payment_code'],
						'payment_method'		=> $row['payment_method'],
						'shipping_address_1'	=> $row['shipping_address_1'],
						'total'					=> $row['total'],
						'date_added'			=> $row['date_added'],
						'shiptor_id'			=> $row['shiptor_id'],
						'delivery_point'		=> $row['delivery_point'],
						'street'				=> $row['street'],
						'house'					=> $row['house'],
						'apartment'				=> $row['apartment'],
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
	
	public function getDateOrdersCourier($valid_period) {
		$sql = 'SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`payment_method`, o.`shipping_address_1`,
		o.`total`, o.`date_added`, so.`shiptor_id`, so.`delivery_point`, so.`address`, so.`street`, so.`house`, so.`apartment`, so.`shipping_method`,
		so.`kladr_id`, so.`time`, so.`weight`, o.`order_status_id`, os.`name` AS status, o.`shipping_city`
		FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
		LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
		LEFT JOIN `' . DB_PREFIX . 'order_status` os ON (o.`order_status_id` = os.`order_status_id`)
		WHERE 1 = 1 AND o.`order_status_id`!=0 ';
		
		$sql .= ' GROUP BY o.`order_id` ORDER BY o.`order_id` DESC, o.`date_added` DESC';
		
		$query = $this->db->query($sql);
		
		$order_periods = array();
		
		foreach($query->rows as $row){
			$shipping_method = $this->getShippingMethod($row['shipping_method']);
			if(!empty($shipping_method)){
				$period = $valid_period + strtotime($row['date_added']);
				if($period > time() 
				&& $row['order_status_id'] == $this->config->get('shipping_shiptor_order_status_for_api') 
				&& $row['shiptor_id'] == 0
				&& (stristr($shipping_method['category'],'-to-'))){
					$order_periods[] = array(
						'order_id'				=> $row['order_id'],
						'firstname'				=> $row['firstname'],
						'lastname'				=> $row['lastname'],
						'email'					=> $row['email'],
						'telephone'				=> $row['telephone'],
						'payment_code'			=> $row['payment_code'],
						'payment_method'		=> $row['payment_method'],
						'shipping_address_1'	=> $row['shipping_address_1'],
						'total'					=> $row['total'],
						'date_added'			=> $row['date_added'],
						'shiptor_id'			=> $row['shiptor_id'],
						'delivery_point'		=> $row['delivery_point'],
						'street'				=> $row['street'],
						'house'					=> $row['house'],
						'apartment'				=> $row['apartment'],
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
	
	public function getDateOrdersCourierGroup($valid_period) {
		$sql = 'SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`payment_method`, o.`shipping_address_1`, o.`shipping_code`, o.`shipping_zone`, o.`shipping_postcode`,
		o.`total`, o.`date_added`, so.`shiptor_id`, so.`delivery_point`, so.`address`, so.`street`, so.`house`, so.`apartment`, so.`shipping_method`, (SELECT `iso_code_2` FROM `' . DB_PREFIX . 'country` WHERE `country_id`=o.shipping_country_id) AS country,
		so.`kladr_id`, so.`time`, so.`weight`, o.`order_status_id`, os.`name` AS status, o.`shipping_city`
		FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
		LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
		LEFT JOIN `' . DB_PREFIX . 'order_status` os ON (o.`order_status_id` = os.`order_status_id`)
		WHERE 1 = 1 AND o.`order_status_id`!=0 ';
		
		$sql .= ' GROUP BY o.`order_id` ORDER BY o.`order_id` DESC, o.`date_added` DESC';
		
		$query = $this->db->query($sql);
		
		$order_periods = array();
		
		foreach($query->rows as $row){
			$shipping_method = $this->getShippingMethod($row['shipping_method']);
			if(!empty($shipping_method)){
				$period = $valid_period + strtotime($row['date_added']);
				if($period > time() 
				&& $row['order_status_id'] == $this->config->get('shipping_shiptor_order_status_for_api') 
				&& $row['shiptor_id'] == 0
				&& (stristr($shipping_method['category'],'-to-'))){
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

		return $order_periods;
	}
	
	public function getPvz($type,$courier) {
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'shipping_shiptor_couriers` WHERE category = "' . $type . '" AND courier = "' . $courier . '"');

		return $query->row;
	}

	public function getOrders($data = array()) {
		$sql = 'SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email`, o.`telephone`, o.`payment_code`, o.`payment_method`, o.`shipping_address_1`,
		o.`total`, o.`date_added`, so.`shiptor_id`, so.`delivery_point`, so.`address`, so.`street`, so.`house`, so.`apartment`, so.`shipping_method`, so.`shipping_method` AS shipping_method_int, so.`date_shipment`, so.`shipment_id`,
		so.`kladr_id`, so.`time`, so.`weight`, o.`order_status_id`, os.`name` AS status, o.`shipping_method`, o.`shipping_city`
		FROM `' . DB_PREFIX . 'shipping_shiptor_orders` so
		LEFT JOIN `' . DB_PREFIX . 'order` o ON (so.`order_id` = o.`order_id`)
		LEFT JOIN `' . DB_PREFIX . 'order_status` os ON (o.`order_status_id` = os.`order_status_id`)
		WHERE 1 = 1 AND o.`order_status_id`!=0 ';

		if (!empty($data['filter_order_id'])) {
			$sql .= ' AND o.order_id = ' . (int)$data['filter_order_id'];
		}
		
		if (isset($data['filter_order_status']) && $data['filter_order_status'] != '' && $data['filter_order_status'] != "*") {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status'] . "'";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}

		if (!empty($data['filter_telephone'])) {
			$sql .= ' AND o.telephone = ' . $data['filter_telephone'];
		}
		
		if (!empty($data['filter_email'])) {
			$sql .= " AND o.email LIKE '%" . urldecode($data['filter_email']) . "%'";
		}
		
		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . urldecode($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= ' AND DATE(o.date_added) = DATE("' . $this->db->escape($data['filter_date_added']) . '")';
		}

		$sql .= ' GROUP BY o.`order_id` ORDER BY o.`order_id` DESC, o.`date_added` DESC';

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= ' LIMIT ' . (int)$data['start'] . ',' . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
		
	public function getTotalOrders($data) {
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'shipping_shiptor_orders so
		LEFT JOIN ' . DB_PREFIX . 'order o ON (so.order_id = o.order_id)
		WHERE 1 = 1';

		if (!empty($data['filter_order_id'])) {
			$sql .= ' AND o.order_id= ' . (int)$data['filter_order_id'];
		}
		
		if (isset($data['filter_order_status']) && $data['filter_order_status'] != '' && $data['filter_order_status'] != "*") {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status'] . "'";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}
		
		if (!empty($data['filter_telephone'])) {
			$sql .= ' AND o.telephone = ' . $data['filter_telephone'];
		}
		
		if (!empty($data['filter_email'])) {
			$sql .= " AND o.email LIKE '%" . urldecode($data['filter_email']) . "%'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . urldecode($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= ' AND DATE(o.date_added) = DATE("' . $this->db->escape($data['filter_date_added']) . '")';
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
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

	public function removeOrder($order_id) {
		$this->db->query('DELETE FROM `' . DB_PREFIX . 'shipping_shiptor_orders` WHERE `order_id` = ' . (int)$order_id);
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
	
	public function productOptionsOrder($order_id, $product_id) {
		$query = $this->db->query('SELECT oo.*, pov.option_value_id FROM `' . DB_PREFIX . 'order_option` oo LEFT JOIN `' . DB_PREFIX . 'product_option_value` pov ON(oo.`product_option_value_id` = pov.`product_option_value_id`) WHERE oo.`order_id` = "' . (int)$order_id . '" AND oo.`order_product_id` =  "' . $product_id . '" ORDER BY pov.`option_id`');

		return $query->rows;
	}
	
	public function getProductMainOption($product_id) {
		$query = $this->db->query('SELECT option_id FROM `' . DB_PREFIX . 'product_option` WHERE `product_id` = "' . (int)$product_id . '" ORDER BY option_id');

		return $query->rows;
	}
	
	public function getProductOptions($product_id, $option_id) {
		$query = $this->db->query('SELECT pov.option_id, ovd.name, pov.price, pov.weight FROM `' . DB_PREFIX . 'product_option_value` pov LEFT JOIN `' . DB_PREFIX . 'option_value_description` ovd ON(pov.`option_value_id` = ovd.`option_value_id`)  WHERE pov.`product_id` = "' . (int)$product_id . '" AND pov.`option_id` = "' . (int)$option_id . '" AND ovd.`language_id` = "' . (int)$this->config->get('config_language_id') . '" ORDER BY pov.option_value_id');

		return $query->rows;
	}
	
	public function getCustomer($data) {
		$sql = 'SELECT a.`address_id`, a.`customer_id`, ssa.`kladr_id`, CONCAT(a.`firstname`, " ", a.`lastname`) AS `recipient`, CONCAT(a.`address_1`, " ", a.`address_2`, " ", a.`city`, " ", a.`postcode`) AS `address`, CONCAT(c.`firstname`, " ", c.`lastname`) AS `customer` FROM `' . DB_PREFIX . 'customer` c LEFT JOIN `' . DB_PREFIX . 'address` a ON (a.address_id = c.address_id) LEFT JOIN `' . DB_PREFIX . 'shipping_shiptor_address`ssa ON (ssa.address_id = a.address_id) WHERE 1';

		if (! empty($data['filter_customer'])) {
			$sql .= ' AND CONCAT(c.`firstname`, " ", c.`lastname`) LIKE "%' . $this->db->escape($data['filter_customer']) . '%"';
		}
		
		if (! empty($data['filter_email'])) {
			$sql .= ' AND c.`email` LIKE "%' . $this->db->escape($data['filter_email']) . '%"';
		}
		
		if (! empty($data['filter_telephone'])) {
			$sql .= ' AND c.`telephone` LIKE "%' . $this->db->escape($data['filter_telephone']) . '%"';
		}
		
		if (! empty($data['filter_kladr_id'])) {
			$sql .= ' AND ssa.`kladr_id` LIKE "%' . $this->db->escape($data['filter_kladr_id']) . '%"';
		}
		
		$sql .= ' ORDER BY a.`customer_id` DESC';

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 50;
			}

			$sql .= ' LIMIT ' . (int)$data['start'] . ',' . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getTotalCustomer($data) {
		$sql = 'SELECT COUNT(*) AS `total` FROM `' . DB_PREFIX . 'customer` c LEFT JOIN `' . DB_PREFIX . 'address` a ON (a.address_id = c.address_id) LEFT JOIN `' . DB_PREFIX . 'shipping_shiptor_address`ssa ON (ssa.address_id = a.address_id) WHERE 1';

		if (! empty($data['filter_customer'])) {
			$sql .= ' AND CONCAT(c.`firstname`, " ", c.`lastname`) LIKE "%' . $this->db->escape($data['filter_customer']) . '%"';
		}
		
		if (! empty($data['filter_email'])) {
			$sql .= ' AND c.`email` LIKE "%' . $this->db->escape($data['filter_email']) . '%"';
		}
		
		if (! empty($data['filter_telephone'])) {
			$sql .= ' AND c.`telephone` LIKE "%' . $this->db->escape($data['filter_telephone']) . '%"';
		}
		
		if (! empty($data['filter_kladr_id'])) {
			$sql .= ' AND ssa.`kladr_id` LIKE "%' . $this->db->escape($data['filter_kladr_id']) . '%"';
		}

		$query = $this->db->query($sql);
		
		return $query->row ? $query->row['total'] : 0;
	}
	
	public function editCustomer($address_id, $customer_id, $kladr_id) {
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'shipping_shiptor_address` SET  `address_id` = ' . (int)$address_id . ', `customer_id` = ' . (int)$customer_id . ', `kladr_id` = "' . $this->db->escape($kladr_id) . '" ON DUPLICATE KEY UPDATE `kladr_id` = "' . $this->db->escape($kladr_id) . '"');
	}
	
	public function removeCustomer($address_id) {
		$this->db->query('DELETE FROM `' . DB_PREFIX . 'shipping_shiptor_address` WHERE `address_id` = ' . (int)$address_id);
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
	
	public function getShiptorId($shiptor_id) {
		return $this->db->query('SELECT shiptor_id FROM `' . DB_PREFIX . 'shipping_shiptor_orders` WHERE `shiptor_id` = ' . (int)$shiptor_id)->row;
	}
	
	public function addOrderHistory($order_id, $order_status_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '0', comment = 'API Shiptor', date_added = NOW()");
	}
}