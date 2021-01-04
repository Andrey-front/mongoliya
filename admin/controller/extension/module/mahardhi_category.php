<?php
class ControllerExtensionModuleMahardhiCategory extends Controller {
	private $error = array();

	public function install() {		
		$this->load->model('mahardhi_category/menu');	
		$this->load->model('user/user_group');

		//add permission
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'mahardhi_category/menu');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'mahardhi_category/menu');
		
		$this->model_mahardhi_category_menu->install();
	}

	public function uninstall() {		
		$this->load->model('mahardhi_category/menu');
		$this->load->model('user/user_group');

		// Remove permission
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'mahardhi_category/menu');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'mahardhi_category/menu');

		$this->model_mahardhi_category_menu->uninstall();
	}

	public function index() {
		$this->load->language('extension/module/mahardhi_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('mahardhi_category', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}



		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');


		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_home'] = $this->language->get('entry_home');
		$data['entry_columns'] = $this->language->get('entry_columns');
		$data['entry_levels'] = $this->language->get('entry_levels');

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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/mahardhi_category', 'user_token=' . $this->session->data['user_token'], true)
		);		

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/mahardhi_category', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/mahardhi_category', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['active_category'])) {
			$data['active_category'] = $this->request->post['active_category'];
		} elseif (!empty($module_info) && isset($module_info['active_category'])) {
			$data['active_category'] = $module_info['active_category'];
		} else {
			$data['active_category'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		// if (isset($this->request->post['homelink'])) {
		// 	$data['homelink'] = $this->request->post['homelink'];
		// } elseif (!empty($module_info)) {
		// 	$data['homelink'] = $module_info['homelink'];
		// } else {
		// 	$data['homelink'] = '';
		// }

		if (isset($this->request->post['columns'])) {
			$data['columns'] = $this->request->post['columns'];
		} elseif (!empty($module_info)) {
			$data['columns'] = $module_info['columns'];
		} else {
			$data['columns'] = '4';
		}

		if (isset($this->request->post['levels'])) {
			$data['levels'] = $this->request->post['levels'];
		} elseif (!empty($module_info)) {
			$data['levels'] = $module_info['levels'];
		} else {
			$data['levels'] = '4';
		}

		$this->load->model('mahardhi_category/menu');

		$data['category_option'] = $this->model_mahardhi_category_menu->getCategoryOption(0); 
		$data['active_category_option'] = $this->model_mahardhi_category_menu->getActiveMenu();
		$data['cms_page_option'] = $this->model_mahardhi_category_menu->getCmsOption(); 
		//$data['information_page_option'] = $this->model_mahardhi_category_menu->getLinkOptions();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mahardhi_category', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/mahardhi_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
