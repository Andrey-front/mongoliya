<?php
# Разработчик: Alex Dovgan
# E-mail: alex@dovgan.spb.su
# Shiptor - Агрегатор служб доставки

class ControllerExtensionPaymentShiptorPaycard extends Controller {
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['text_loading'] = $this->language->get('text_loading');

		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/shiptor_paycard', $data);
	}

	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'shiptor_paycard') {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_shiptor_paycard_order_status_id'));
		}
	}
}