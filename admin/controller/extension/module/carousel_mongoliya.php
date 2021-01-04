<?php
class ControllerExtensionModuleCarouselMongoliya  extends Controller {
	private $error = array(); // This is used to set the errors, if any.
	
	public function index() {
		$this->load->language('extension/module/carousel_mongoliya');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				if (!isset($this->request->get['module_id'])) {
						$this->model_setting_module->addModule('carousel_mongoliya', $this->request->post);
				} else {
						$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
				}

				$this->session->data['success'] = $this->language->get('text_success');

				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if(isset($this->session->data['success']) && !empty($this->session->data['success'])){
			$data['success'] = $this->session->data['success'];
			$this->session->data['success'] = '';
		}

		if(isset($this->session->data['failure']) && !empty($this->session->data['failure'])){
			$data['failure'] = $this->session->data['failure'];
			$this->session->data['failure'] = '';
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
						'href' => $this->url->link('extension/module/carousel_mongoliya', 'user_token=' . $this->session->data['user_token'], true)
				);
		} else {
				$data['breadcrumbs'][] = array(
						'text' => $this->language->get('heading_title'),
						'href' => $this->url->link('extension/module/carousel_mongoliya', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
				);
		}

		if (!isset($this->request->get['module_id'])) {
				$data['action'] = $this->url->link('extension/module/carousel_mongoliya', 'user_token=' . $this->session->data['user_token'], true);
		} else {
				$data['action'] = $this->url->link('extension/module/carousel_mongoliya', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		$data['action_add'] = 	$this->url->link('extension/module/carousel_mongoliya/add', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);

		$data['edit'] = $this->url->link('extension/module/carousel_mongoliya/edit', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);

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
		$this->load->model('extension/module/carousel_mongoliya');
		$data['banners'] = $this->model_extension_module_carousel_mongoliya->getBanners();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/carousel_mongoliya', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/carousel_mongoliya')) {
				$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
				$this->error['name'] = $this->language->get('error_name');
		}
		return !$this->error;
	}

	public function edit(){
		if (isset($this->request->get['id']) && (intval($this->request->get['id']) > 0)) {

			$id = intval($this->request->get['id']);
		
			$this->load->model('extension/module/carousel_mongoliya');
			// Loading the language file of helloworld
			$this->load->language('extension/module/carousel_mongoliya');

			$this->document->setTitle($this->language->get('heading_title_edit'));
	
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
					'text' => $this->language->get('text_home'),
					'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['breadcrumbs'][] = array(
					'text' => $this->language->get('text_extension'),
					'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=extension', true)
			);

			$data['banner'] = $this->model_extension_module_carousel_mongoliya->getBannerById($id);
			$data['token'] = $this->session->data['user_token'];
			$data['action'] = 	$this->url->link('extension/module/carousel_mongoliya/update', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
			$data['submit'] = 'Редактировать';
			
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			$this->response->setOutput($this->load->view('extension/module/carousel_mongoliya_form', $data));
		}
		
	}

	public function add(){
		
		$this->load->language('extension/module/carousel_mongoliya');

		$this->document->setTitle($this->language->get('heading_title_add'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=extension', true)
		);

		$data['token'] = $this->session->data['user_token'];
		$data['action'] = $this->url->link('extension/module/carousel_mongoliya/save', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
	
		$data['submit'] = 'Сохранить';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/carousel_mongoliya_form', $data));

	}

	public function save(){
		$this->load->language('extension/module/carousel_mongoliya');

		$library = 'imageupload';
		$file = DIR_SYSTEM . 'library/' . str_replace('../', '', (string)$library) . '.php';

		if (file_exists($file)) {
			include_once($file);
		} else {
				trigger_error('Error: Could not load library ' . $file . '!');
				exit();
		}

		$data['caption'] = filter_var( $this->request->post['caption'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['msg'] = filter_var( $this->request->post['msg'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['link'] = filter_var( $this->request->post['link'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['link_text'] = filter_var( $this->request->post['link_text'], FILTER_SANITIZE_SPECIAL_CHARS);

		$data['photo'] = '';

		if(isset($_FILES['photo']) && $_FILES['photo']['name']!=""){
			$handle = new Verot\Upload\Upload($_FILES['photo']);

			$strtotime = strtotime("now");
	
			$data['photo'] .= $strtotime;
			
			if ($handle->uploaded) {
				
				$data['photo'] .= '.'.$handle->file_src_name_ext;
				$handle->file_new_name_body   = $strtotime;
				$handle->process(DIR_IMAGE.'catalog/carousel_mongoliya/');
				$handle->processed;
				
				$handle->clean();
			}
		}
		
		$this->load->model('extension/module/carousel_mongoliya');
		$result = $this->model_extension_module_carousel_mongoliya->save($data);
		
		if($result){
			$this->session->data['success'] = $this->language->get('text_success_add');
		}else{
			$this->session->data['failure'] = $this->language->get('text_failure');
		}
		$this->response->redirect($this->url->link('extension/module/carousel_mongoliya', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true));	

	}

	public function update(){
		$this->load->language('extension/module/carousel_mongoliya');

		$library = 'imageupload';
		$file = DIR_SYSTEM . 'library/' . str_replace('../', '', (string)$library) . '.php';

		if (file_exists($file)) {
			include_once($file);
		} else {
				trigger_error('Error: Could not load library ' . $file . '!');
				exit();
		}
		$data['id'] = (int) $this->request->post['id'];
		$data['caption'] = filter_var( $this->request->post['caption'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['msg'] = filter_var( $this->request->post['msg'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['link'] = filter_var( $this->request->post['link'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['link_text'] = filter_var( $this->request->post['link_text'], FILTER_SANITIZE_SPECIAL_CHARS);

		if(isset($_FILES['photo']) && $_FILES['photo']['name']!=""){
			$handle = new Verot\Upload\Upload($_FILES['photo']);

			$strtotime = strtotime("now");
	
			$data['photo'] = $strtotime;
			
			if ($handle->uploaded) {
				
				$data['photo'] .= '.'.$handle->file_src_name_ext;
				$handle->file_new_name_body   = $strtotime;
				$handle->process(DIR_IMAGE.'catalog/carousel_mongoliya/');
				$handle->processed;
				
				$handle->clean();
			}
		}
		
		$this->load->model('extension/module/carousel_mongoliya');
		$result = $this->model_extension_module_carousel_mongoliya->update($data);
		
		if($result){
			$this->session->data['success'] = $this->language->get('text_success_update');
		}else{
			$this->session->data['failure'] = $this->language->get('text_failure');
		}
		$this->response->redirect($this->url->link('extension/module/carousel_mongoliya', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true));	

	}

	public function delete(){
		if($this->checkAjax()){
			$id = (int) $this->request->post['id'];

			$this->load->model('extension/module/carousel_mongoliya');
			
			$banner = $this->model_extension_module_carousel_mongoliya->getBannerById($id);
			
			$photoOld = DIR_IMAGE.'catalog/carousel_mongoliya/'.$banner['photo'];
			
			if (file_exists($photoOld)) {
				unlink($photoOld);
			}

			$res = $this->model_extension_module_carousel_mongoliya->delete($id);

			$this->response->addHeader('Content-Type: application/json');

			$this->response->setOutput(json_encode($res));
		}
	}

	public function install() {

		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/carousel_mongoliya');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/carousel_mongoliya');
		
		$this->load->model('extension/module/carousel_mongoliya');

		$this->model_extension_module_carousel_mongoliya->install();

	}

	public function uninstall() {
 
    $this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('reviews');

		$this->load->model('user/user_group');

		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/carousel_mongoliya');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/carousel_mongoliya');
		
		$this->load->model('extension/module/carousel_mongoliya');

		$this->model_extension_module_carousel_mongoliya->uninstall();
		
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
