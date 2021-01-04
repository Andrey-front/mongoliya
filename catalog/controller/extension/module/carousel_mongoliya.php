<?php
class ControllerExtensionModuleCarouselMongoliya extends Controller {
	public function index() {
		
    $this->document->addScript('catalog/view/javascript/carousel_mongoliya.js');

		$this->load->model('extension/module/carousel_mongoliya');

		$data['banners'] = $this->model_extension_module_carousel_mongoliya->getBanners();
		
		return $this->load->view('extension/module/carousel_mongoliya', $data);
  }
	
}