<?php
class ControllerExtensionModuleReviews extends Controller {
	public function index() {
		
    $this->document->addStyle('catalog/view/stylesheet/reviews.css');
		$this->document->addScript('catalog/view/javascript/reviews.js');
		
		$this->load->language('extension/module/reviews');
		$data['heading_title'] = $this->language->get('heading_title');
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $data['heading_title'],
			'href' => $this->url->link('extension/module/reviews')
		);

		$this->document->setTitle($data['heading_title']);
		$this->document->setDescription($data['heading_title']);

		$this->load->model('extension/module/reviews');

		$reviews_total = $this->model_extension_module_reviews->getTotalReviews();
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$limit = 10;

		if($reviews_total > $limit) {
			$pagination = new Pagination();
			$pagination->total = $reviews_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('extension/module/reviews', '&page={page}');
			$data['pagination'] = $pagination->render();
		}
		
		$data['reviews'] = $this->model_extension_module_reviews->getReviews([
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		]);
		
		for ($i=0; $i <count($data['reviews']); $i++) {
			
			if($data['reviews'][$i]['photo']){
				$data['reviews'][$i]['photo'] = '/image/catalog/reviews/'.$data['reviews'][$i]['photo'];
			}
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		return $this->response->setOutput($this->load->view('extension/module/reviews', $data));
  }
  
  public function addReview(){
		$library = 'imageupload';
		$file = DIR_SYSTEM . 'library/' . str_replace('../', '', (string)$library) . '.php';

		if (file_exists($file)) {
				include_once($file);
		} else {
				trigger_error('Error: Could not load library ' . $file . '!');
				exit();
		}
				
		$data['name'] = filter_var( $this->request->post['name'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['phone'] = filter_var( $this->request->post['phone'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['city'] = filter_var( $this->request->post['city'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['msg'] = filter_var( $this->request->post['msg'], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['mark'] = (int) $this->request->post['mark'];
		$data['photo'] = '';

		if(isset($_FILES['photo']) && $_FILES['photo']['name']!=""){
			$handle = new Verot\Upload\Upload($_FILES['photo']);

			$strtotime = strtotime("now");
	
			$data['photo'] .= $strtotime;
			
			if ($handle->uploaded) {
				
				$data['photo'] .= '.'.$handle->file_src_name_ext;
				$handle->file_new_name_body   = $strtotime;
				$handle->image_resize         = true;
				$handle->image_x              = 120;
				$handle->image_ratio_y        = true;
				$handle->process(DIR_IMAGE.'catalog/reviews/');
				$handle->processed;
				
				$handle->clean();
			}
		}
		
		$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

		if($captcha){
			$this->response->addHeader('Content-Type: application/json');
			$json['error'] = 'Проверочный код не совпадает с изображением! ';
		
			$this->response->setOutput(json_encode($json));
		}

		$this->load->model('extension/module/reviews');
		
    $json['review'] = $this->model_extension_module_reviews->saveReciew($data);

		$this->response->addHeader('Content-Type: application/json');
		$json['success'] = true;
	
		$this->response->setOutput(json_encode($json));
  }

	public function getLatestReviews(){
		$limit = (int) $this->request->post['limit'];
		
		$this->load->model('extension/module/reviews');
		$json['reviews'] =  $this->model_extension_module_reviews->getReviews([
			'start' => 0,
			'limit' => $limit
		]);

		echo json_encode($json);
	}
	
}