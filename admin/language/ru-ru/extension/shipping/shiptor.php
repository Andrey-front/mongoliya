<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

$_['text_version']			= ' [ версия: 3.3.1 ]';
$_['heading_title']    	    = 'Shiptor - Надежная служба доставки';

$_['text_extension']		= 'Расширения';
$_['text_edit']             = 'Редактирование модуля';
$_['text_faq']				= 'Документация';
$_['text_shipping']			= 'Доставка';
$_['text_success']			= 'Данные модуля обновлены!';
$_['text_warning']			= 'Ошибка, данные модуля не найдены!';
$_['text_loading']			= 'Загрузка...';
$_['text_not_selected']		= 'Нет';
$_['text_not_use']		    = 'Не использовать';
$_['text_article']			= 'Артикул';
$_['text_model']		    = 'Модель';
$_['text_size_standart']	= 'Стандартная посылка';
$_['text_size_auto']		= 'Автоматический расчет';

$_['text_module_setting'] 	= 'Настройки';
$_['text_order_customer'] 	= 'Адреса покупателей';
$_['text_order_shiptor'] 	= 'Заказы Shiptor';
$_['text_order_store'] 		= 'Заказы магазина';

$_['tab_general']			= 'Методы доставки';
$_['tab_delivery']			= 'Параметры доставки';
$_['tab_setting']			= 'Настройки';

$_['button_authorization']	= 'Активировать модуль';
$_['button_export']			= 'Выгрузить';

# Default settings
$_['entry_authorization']	= 'Ключ API';
$_['entry_country']    		= 'Страна';
$_['entry_region']    		= 'Регионы';
$_['entry_logger']    		= 'Режим отладки';
$_['entry_status']			= 'Включение модуля';
$_['entry_sort_order']		= 'Порядок сортировки';
$_['entry_length']          = 'Длина, см';
$_['entry_width']           = 'Ширина, см';
$_['entry_height']          = 'Высота, см';
$_['entry_weight']          = 'Вес товара по умолчанию, кг';
$_['entry_weight_class_id'] = 'Ед. измерения веса';
$_['entry_default_dimensions'] = 'Измерения по умолчанию';
$_['entry_days']            = 'Сроки доставки';
$_['entry_time']            = 'Выбор времени доставки';
$_['entry_cache']           = 'Кеширование';
$_['entry_comment']         = 'Комментарий';
$_['entry_my_comment']      = 'Мой комментарий';
$_['entry_product'] 		= 'Товары';
$_['entry_method_shipping'] = 'Передать доставку в Shiptor';
$_['entry_order_status'] 	 = 'Статус';
$_['entry_all_order_status'] 	 = 'Все статусы';
$_['entry_order_status_for_api'] = 'Статус заказа для передачи в Шиптор';
$_['entry_order_status_after_api'] = '<b style="font-size:13px;">Статусы заказа после передачи в Shiptor</b>';
$_['entry_validate_period']	= 'Возраст заказа для передачи в Shiptor';
$_['entry_auto_send']           = 'Автоматически передавать заказы в Shiptor';
$_['entry_setting_line_delivery']	= 'Настройки прямой доставки';
$_['entry_setting_city_delivery']	= 'Город отправителя';
$_['entry_setting_fio_delivery']	= 'ФИО';
$_['entry_setting_address_delivery']	= 'Адрес отправителя';
$_['entry_setting_telephone_delivery']	= 'Телефон отправителя';
$_['entry_setting_email_delivery']	= 'Email отправителя';
$_['entry_setting_postponement_delivery']	= 'Отсрочка отгрузки';
$_['entry_setting_fence_interval_delivery']	= 'Интервал забора по курьеру';
$_['entry_pvz']	= 'ПВЗ отгрузки';
$_['entry_shipping_name']	= 'Отображаемое название';
$_['entry_id_product']	= 'Идентификатор товара';
$_['entry_export_product']	= 'Выгрузить данные по товарам в Shiptor';
$_['entry_size']	= 'Габариты посылки';

# Status
$_['entry_order_status_after_new']							= 'Новая';
$_['entry_order_status_after_waiting_pickup']				= 'Ожидает забора';
$_['entry_order_status_after_waiting_send']					= 'Ожидает отгрузки';
$_['entry_order_status_after_arrived_to_warehouse']			= 'Прибыла на склад';
$_['entry_order_status_after_packed']						= 'Упакована';
$_['entry_order_status_after_prepared_to_send']				= 'Готова к отправке';
$_['entry_order_status_after_sent']							= 'Отправлена';
$_['entry_order_status_after_delivered']					= 'Доставлена';
$_['entry_order_status_after_waiting_on_delivery_point']	= 'Ожидает в пункте выдачи';
$_['entry_order_status_after_removed']						= 'Удалена';
$_['entry_order_status_after_returned']						= 'Готова к возврату';
$_['entry_order_status_after_reported']						= 'Возвращена';
$_['entry_order_status_after_lost']							= 'Утеряна';
$_['entry_order_status_after_resend']						= 'Отправлена повторно';

$_['help_authorization']	= 'Ключ для работы с API можно найти в <a class="help_authorization" href="https://shiptor.ru/account/settings/api" target="_blank">личном кабинете</a>';
$_['help_country']    		= 'Код страны для расчета';
$_['help_region']    		= 'Дополнительные настройки для отдельных регионов';
$_['help_logger']    		= 'Запись результатов запроса в журнал ошибок';
$_['help_time']             = 'Применяется только для доставки Shiptor';
$_['help_comment']          = 'Комментарий API';
$_['help_my_comment']       = 'Работает, только при отключении комментария по API';
$_['help_dimensions']		= 'Используется, если у товара не задан вес';
$_['help_default_dimensions']	= 'длина Х ширина Х высота (см)';
$_['help_weight_class_id']  = 'Выбрать килограммы';
$_['help_product']  		= 'Передача товаров в систему Shiptor - только пользователям которым доступен fulfilment';
$_['help_method_shipping']  = 'Необходимо для отправления заказов в систему Shiptor';
$_['help_increase_days']    = 'Увеличить на';

# Shipping settings
$_['entry_shipping_status'] = 'Статус';
$_['entry_shipping_time']   = 'Время';

$_['help_shipping_status']  = 'Отображение метода доставки';
$_['help_shipping_time']    = 'Время, до которого будет отображаться доставка';

# Region settings
$_['entry_region_status']  = 'Статус';
$_['help_region_status']   = 'Использует настройки по данному региона. Если опция отключена, применяются настройки по умолчанию.';

$_['entry_region_hide']    = 'Отображение';
$_['help_region_hide']     = 'Скрывает метод доставки в этом регионе';

# Geo zone settings	
$_['text_percent'] 			= 'Проценты';
$_['text_amount'] 			= 'Фиксированная сумма';

$_['entry_setting'] 		= 'Настройки';
$_['entry_weight_price'] 	= 'Цена от веса';
$_['entry_total_free'] 		= 'Бесплатная доставка';
$_['entry_total_min'] 		= 'Минимальная цена';
$_['entry_total_max'] 		= 'Максимальная цена';
$_['entry_total_min_weight'] = 'Минимальный вес';
$_['entry_total_max_weight'] = 'Максимальный вес';
$_['entry_fixed'] 			= 'Фиксированная цена';
$_['entry_type'] 			= 'Тип наценки';
$_['entry_markup'] 			= 'Наценка';
$_['entry_markup_surcharge'] = 'Наценка наложенного платежа';

$_['help_setting']			= 'Выберете один из методов, который будет использован';
$_['help_weight_price']		= 'Например: <code>5:300, 10:600</code> <code>вес:цена</code>';
$_['help_total_free']		= 'Максимальная сумма, при которой доставка будет бесплатной.';
$_['help_total_min']		= 'Минимальная сумма, при которой доставка будет доступна.';
$_['help_total_max']		= 'Максимальная сумма, при которой доставка будет не доступна.';

$_['help_total_min_weight']	= 'Минимальный вес, при которой доставка будет доступна.';
$_['help_total_max_weight'] = 'Максимальный вес, при которой доставка будет не доступна.';
$_['help_fixed']			= 'Фиксированная стоимость доставки';
$_['help_type']				= 'Тип наценки, фиксированная сумма или в процентах';
$_['help_markup']			= 'Наценка стоимости доставки в зависимости от выбранного типа';
$_['help_markup_surcharge']	= 'Добавляется к модулю <code>Учет суммы наложенного платежа</code>';
$_['help_status']			= '';

# Order Store
$_['text_pickup']     		= 'Самовывоз';
$_['text_courier']    		= 'Курьер';
$_['text_pickup']     		= 'Самовывоз';
$_['text_in_cash']    		= 'Наличными';
$_['text_in_card']    		= 'Картой';

$_['column_order_id']   	= '№ Заказа ';
$_['column_shiptor_id'] 	= '№ Shiptor';
$_['column_point_address']  = 'Адрес ПВЗ';
$_['column_city']           = 'Город';
$_['column_customer']   	= 'Покупатель';
$_['column_email']   		= 'E-mail';
$_['column_telephone']   	= 'Телефон';
$_['column_street']     	= 'Улица';
$_['column_house']      	= 'Дом';
$_['column_apartment'] 		= 'Квартира';
$_['column_delivery']   	= 'Доставка';
$_['column_payment']	    = 'Оплата';
$_['column_cod']	    	= 'Нал-ный платеж';
$_['column_date_added']		= 'Дата';
$_['column_date_shipment']	= 'Дата отгрузки';
$_['column_checkpoints']	= 'Статусы доставки';
$_['column_problems']		= 'Проблемы';
$_['column_confirmed']		= 'Подтверждена прямая';
$_['column_shipment_id']	= '№ Отгрузки';
$_['column_external_tracking_number']	= 'Трекномер курьерской службы';
$_['column_status']		    = 'Статус';

# Order Shiptor
$_['column_label']		 	= 'Наклейка';
$_['column_tracking']	 	= 'Трек номер Shiptor';
$_['column_weight']	     	= 'Вес. кг';
$_['column_limits']	     	= 'Макс. габариты';
$_['column_time']	     	= 'Время доставки';
$_['column_status']	     	= 'Статус Shiptor';
$_['column_history']	    = 'История';


# Alert
$_['error_permission']	  	= 'У Вас нет прав для управления этим модулем!';
$_['error_authorization'] 	= 'Необходимо заполнить ключ API';
$_['error_numeric']		 	= 'Значение должно быть числом и больше 0';
$_['error_warning']		 	= 'Внимательно проверьте форму на ошибки';
$_['error_extension']	  	= 'Для работы модуля требуется PHP расширение cURL';
$_['error_key']			  	= 'Неверный ключ API';
$_['error_setting']		  	= 'Непредвиденная ошибка, попробуйте ещё раз';
$_['error_default_weight']  	= 'Необходимо указать вес по умолчанию';
$_['error_default_dimensions']  	= 'Необходимо указать все три размера';

$_['error_all']				= '<b class="text-danger">Неверный ключ API. Проверьте ключ в <a class="help_authorization" href="https://shiptor.ru/account/settings/api" target="_blank">личном кабинете</a></b>';
$_['error_is_numeric']		= 'Значение должно быть числом';
$_['error_is_count']	    = 'Ошибка не указано кол-во товара';
$_['error_is_price']	    = 'Ошибка не указана цена товара';
$_['error_is_telephone']	= 'Ошибка номера телефона! неверный формат / поле не заполнено';
$_['error_is_email']		= 'Ошибка E-mail! неверный формат / поле не заполнено';
$_['error_is_shipping']     = 'Ошибка метод доставки! неверный формат';
$_['error_is_street']		= 'Ошибка адреса покупателя! неверный формат / поле не заполнено';
$_['error_is_house']		= 'Ошибка номера дома! не верный формат / поле не заполнено';
$_['error_is_name']			= 'Ошибка имени покупателя! неверный формат / значение меньше 3 символов / поле не заполнено';
$_['error_is_surname']		= 'Ошибка фамилии покупателя! неверный формат / значение меньше 3 символов / поле не заполнено';
$_['error_is_order']		= 'Ошибка номера заказа! обновите страницу и попытайтесь отправить снова';
$_['error_is_shiptor']		= 'Ошибка не определена! проверьте журнал ошибок в режиме отладки';
$_['error_is_weight']		= 'Ошибка веса! поле должно быть числом';
$_['error_is_kladr']		= 'Ошибка кода кладр! поле должно быть числом';
$_['error_is_markup']		= 'Наценка или скидка не может быть более 100%';
$_['error_is_time']         = 'Не позднее 12:00 и не ранее 21:00';
$_['error_is_count']        = 'Нет заказов для отправки в Shiptor!';
$_['error_is_county']        	= 'Не указана Страна в общих настройках Opencart!';
$_['error_is_zone']        		= 'Не указана Область в общих настройках Opencart!';
$_['error_is_city']        		= 'Не указан Город в "Настройках прямой доставки"!';
$_['error_is_address']        	= 'Не указан Адрес в "Настройках прямой доставки" или в общих настройках Opencart!';
$_['error_is_delivery_point']	= 'Не указан ПВЗ отгрузки в настройках "Методы доставки"!';
$_['error_is_date']				= 'Не указана Отсрочка отгрузки в "Настройках прямой доставки"!';
$_['error_is_postal_cod']		= 'Почтовый код должен состоять из 6 цифр!';

$_['success_shiptor']	  	= 'Данные о доставке успешно отправлены';
$_['success_remove']	  	= 'Удаление прошло успешно';
$_['success_update']	  	= 'Редактирование прошло успешно';
$_['success_method_add']    = 'Данные об услуге добавлены в личный кабинет';

# Order customer
$_['column_recipient']      = 'Получатель';
$_['column_address']        = 'Адрес получателя';
$_['column_kladr_id']       = 'Код кладр';

$_['text_filter_customer']   = 'Покупатель';
$_['text_filter_email']      = 'E-mail';
$_['text_filter_telephone']  = 'Телефон';
$_['text_filter_kladr_id']   = 'Код кладр';
$_['text_filter_date_added'] = 'Дата добавления';
