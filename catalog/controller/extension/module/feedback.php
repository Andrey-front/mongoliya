<?php
class ControllerExtensionModuleFeedback extends Controller {

	public function index($setting) {

		$this->document->addStyle('catalog/view/stylesheet/feedback.css');
		$this->document->addScript('catalog/view/javascript/jquery.magnific-popup-inline.js');
		$this->document->addScript('catalog/view/javascript/feedback.js');

		return $this->load->view('extension/module/feedback');
	}

	public function success(){
		$this->load->language('extension/module/feedback');

		$data['text_success'] = $this->language->get('text_success');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

		$data['continue'] = $this->url->link('common/home');
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('extension/module/success', $data));
	}

	public function ajaxContact(){
		$this->load->model('extension/module/feedback');
		$info = $this->model_extension_module_feedback->getSettings();
		
		$json =[];
		$phone = stripcslashes($this->request->post['phone']);
		$data['title'] = $info['name'];
		$data['body'] = "<p>НТелефон - ". $phone."</p>";

		$this->sendEmail($data);

		$this->response->addHeader('Content-Type: application/json');
		$json['success'] = true;
	
		$this->response->setOutput(json_encode($json));
	}
		
	private function sendEmail(array $data){
		
		$data['store_name'] = $this->config->get('config_name');
		$data['store_url'] = $this->config->get('config_email');

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($this->config->get('config_email'));
		$mail->setFrom('info@0250.ru');
		$mail->setReplyTo($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $data['title']), ENT_QUOTES, 'UTF-8'));

		$mail->setText($data['body']);
		return $mail->send();

	}
}