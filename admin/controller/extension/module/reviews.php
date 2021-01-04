<?php
class ControllerExtensionModuleReviews extends Controller {
	private $error = array(); // This is used to set the errors, if any.
	
	public function index() {
		$this->load->language('extension/module/reviews');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				if (!isset($this->request->get['module_id'])) {
						$this->model_setting_module->addModule('reviews', $this->request->post);
				} else {
						$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
				}

				$this->session->data['success'] = $this->language->get('text_success');

				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
				$data['error_warning'] = $this->error['warning'];
		} else {
				$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
				$data['error_name'] = $this->error['name'];
		} else {
				$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=extension', true)
		);

		if (!isset($this->request->get['module_id'])) {
				$data['breadcrumbs'][] = array(
						'text' => $this->language->get('heading_title'),
						'href' => $this->url->link('extension/module/reviews', 'user_token=' . $this->session->data['user_token'], true)
				);
		} else {
				$data['breadcrumbs'][] = array(
						'text' => $this->language->get('heading_title'),
						'href' => $this->url->link('extension/module/reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
				);
		}

		if (!isset($this->request->get['module_id'])) {
				$data['action'] = $this->url->link('extension/module/reviews', 'user_token=' . $this->session->data['user_token'], true);
		} else {
				$data['action'] = $this->url->link('extension/module/reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=extension', true);

		if (isset($this->request->get['module_id']) 
		&& ($this->request->server['REQUEST_METHOD'] != 'POST')) {
				$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
				$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
				$data['name'] = $module_info['name'];
		} else {
				$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
				$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
				$data['status'] = $module_info['status'];
		} else {
				$data['status'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];
		$this->load->model('extension/module/reviews');

		$reviews_total = $this->model_extension_module_reviews->getTotalReviews();
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;
		$pagination = new Pagination();
		$pagination->total = $reviews_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('extension/module/reviews', 
		'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['reviews'] = $this->model_extension_module_reviews->getReviews([
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		]);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/reviews', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/reviews')) {
				$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
				$this->error['name'] = $this->language->get('error_name');
		}
		return !$this->error;
	}

	public function approve(){
		if($this->checkAjax()){

			$data['id'] = (int) $this->request->post['id'];
			$data['status'] = 1;
			$this->load->model('extension/module/reviews');

			$result = $this->model_extension_module_reviews->update($data);

			$this->response->addHeader('Content-Type: application/json');

			$this->response->setOutput(json_encode($result));
		}
	}

	public function deleteReview(){
		if($this->checkAjax()){
			$id = (int) $this->request->post['id'];

			$this->load->model('extension/module/reviews');
			
			$review = $this->model_extension_module_reviews->getReviewById($id);
			
			$photoOld = DIR_IMAGE.'catalog/reviews/'.$review['photo'];
			
			if (file_exists($photoOld)) {
				unlink($photoOld);
			}

			$this->response->addHeader('Content-Type: application/json');
			
			$res = $this->model_extension_module_reviews->delete($id);

			$this->response->setOutput(json_encode($res));
		}
	}

	public function install() {

		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/reviews');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/reviews');
		
		$this->load->model('extension/module/reviews');

		$this->model_extension_module_reviews->install();

	}

	public function uninstall() {
 
    $this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('reviews');

		$this->load->model('user/user_group');

		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/reviews');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/reviews');
		
		$this->load->model('extension/module/reviews');

		$this->model_extension_module_reviews->uninstall();
		
	}

	private function checkAjax(): bool
	{
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
					strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
					return true;
			}
			
			return false;
	}

}
