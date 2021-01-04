<?php

use Boxberry\Client\Client;

if (!class_exists('Client')) {
    require_once DIR_SYSTEM . 'library/boxberry/autoload.php';
}

class ControllerEventBoxberry extends Controller
{
    public function addOrderHistory(&$route, &$args)
    {
        if (empty($this->cart->getProducts($args[0]))){
            return;
        }

        $this->load->model('checkout/order');
        $this->load->model('boxberry/delivery');
        $this->load->model('catalog/product');

        $order_info = $this->model_checkout_order->getOrder($args[0]);


        if (!in_array($order_info['shipping_code'], [
            'boxberry.pickup', 'boxberry.courier_delivery', 'boxberry.pickup_prepaid', 'boxberry.courier_delivery_prepaid'
        ])) {
            return;
        }

        if ($order_info && $args[1]) {

            $orderId = (int)$args[0];

            $boxberryDelivery = $this->model_boxberry_delivery->getDelivery($order_info['order_id']);

            $city = $order_info['shipping_city'];
            $index = $order_info['shipping_postcode'];
            $region = $order_info['shipping_zone'];
            $address = $order_info['shipping_address_1'];
            $email = $order_info['email'];
            $phone = $order_info['telephone'];

            $receiver = $order_info['lastname']. ' '.$order_info['firstname'];

            $im_id = null;
            $label = null;

            $boxberry_to_point = '';
            if (strpos($order_info['shipping_address_2'], '#') !== false) {
                $point =  trim(str_replace('#', '', $order_info['shipping_address_2']));
                if (!empty($point)) {
                    $boxberry_to_point = $point;
                }
            }
            if (strpos($order_info['payment_address_2'], '#') !== false) {
                $point =  trim(str_replace('#', '', $order_info['payment_address_2']));
                if (!empty($point)) {
                    $boxberry_to_point = $point;
                }
            }


            if ($boxberryDelivery === null) {
            } else {
                $im_id = $boxberryDelivery['im_id'];
                $label = $boxberryDelivery['label'];
            }

            $error = '';

            if ((($args[1] == $this->config->get('shipping_boxberry_order_status')))) {
                $client = new \Boxberry\Client\Client();
                $client->setKey($this->config->get('shipping_boxberry_api_token'));
                $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));

                $parselCreate = $client->getParselCreate();
                $parsel = new  \Boxberry\Models\Parsel();
                if ($boxberryDelivery) {
                    $parsel->setTrack($boxberryDelivery['im_id']);
                }
                $parsel->setOrderId($order_info['order_id']);

                $parsel->setPrice($order_info['total']);
                $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . $orderId . "' ORDER BY sort_order ASC");


                $order_totals = $query->rows;

                foreach ($order_totals as $total) {
                    $totals[$total['code']] = $total['value'];
                }

                $delivery_cost = isset($totals['shipping']) ?
                    $this->currency->convert($totals['shipping'], $order_info['currency_code'], 'RUB') : 0;
                $parsel->setDeliverySum($delivery_cost);
                if (
                    $order_info['shipping_code'] == 'boxberry.courier_delivery_prepaid' ||
                    $order_info['shipping_code'] == 'boxberry.pickup_prepaid'
                ) {
                    $parsel->setPaymentSum(0);
                } else {

                    $parsel->setPaymentSum($this->currency->convert($order_info['total'], $order_info['currency_code'], 'RUB'));
                }

                $customer = new \Boxberry\Models\Customer();
                $customer->setFio($receiver);
                $customer->setEmail($email);
                $customer->setPhone($phone);
                $customer->setAddress($region . ', ' . $city . ', ' . $address);
                $parsel->setCustomer($customer);

                $items = new \Boxberry\Collections\Items();
                $orderProductsQuery = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . $orderId . "'");
                $orderItems = $orderProductsQuery->rows;

                foreach ($orderItems as $key => $orderItem) {
                    $item = new \Boxberry\Models\Item();
                    $item->setId($orderItem['product_id']);
                    $item->setName($orderItem['name']);
                    $item->setPrice($this->currency->convert($orderItem['price'], $this->config->get('config_currency'), 'RUB'));
                    $item->setQuantity($orderItem['quantity']);
                    $items[] = $item;
                }
                $parsel->setItems($items);

                $parsel->setWeights(['weight' => $this->weight->convert($this->cart->getWeight(),
                    $this->config->get('config_weight_class_id'), $this->config->get('shipping_boxberry_weight_class_id'))]);
                $shop = array(
                    'name' => '',
                    'name1' => $this->config->get('shipping_boxberry_from_point')
                );

                if ($order_info['shipping_code'] == 'boxberry.courier_delivery_prepaid'
                    || $order_info['shipping_code'] == 'boxberry.courier_delivery') {
                    $parsel->setVid(2);
                    $courierDost = new \Boxberry\Models\CourierDelivery();
                    $courierDost->setIndex($index);
                    $courierDost->setCity($city);
                    $courierDost->setAddressp($address);
                    $parsel->setCourierDelivery($courierDost);
                } else {
                    $parsel->setVid(1);
                    if (strlen($boxberry_to_point) === 0) {

                    }
                    $shop['name'] = $boxberry_to_point;
                }
                $parsel->setShop($shop);
                $parselCreate->setParsel($parsel);

                try {
                    $answer = $client->execute($parselCreate);
                    $im_id = $answer->getTrack();
                    $label = $answer->getLabel();

                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                $data = array(
                    'order_id' => $order_info['order_id'],
                    'im_id' => $im_id,
                    'label' => $label,
                    'boxberry_to_point' => $boxberry_to_point,
                    'address' => $address,
                    'error' => $error
                );
                $this->model_boxberry_delivery->addDelivery($data);
            }
        }
    }
}