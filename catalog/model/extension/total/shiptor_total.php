<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ModelExtensionTotalShiptorTotal extends Model {
	public function getTotal($total) {
		$this->load->language('extension/total/shiptor_total');

		if (isset($this->session->data['payment_method'], $this->session->data['shipping_method'], $this->session->data['shipping_method']['surcharge'])) {
			
			if ($this->session->data['payment_method']['code'] == 'shiptor_pay' || $this->session->data['payment_method']['code'] == 'shiptor_paycard') {

				if (!empty($this->session->data['shipping_method']['surcharge'])) {
					$surcharge = $this->session->data['shipping_method']['surcharge'];

					$total['totals'][] = array(
						'code'		=> 'shiptor_total',
						'title'		=> $this->language->get('text_title'),
						'value'		=> $surcharge,
						'sort_order'	=> $this->config->get('total_shiptor_total_sort_order'),
					);

					$total['total'] += $surcharge;
				}
			}
		}
	}
}