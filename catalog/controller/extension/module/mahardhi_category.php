<?php
class ControllerExtensionModuleMahardhiCategory extends Controller {
	
	private $pattern = '/^([a-z_]*)[0-9]+/';

	public function index($setting) {
		
		$this->language->load('extension/module/mahardhi_category');
      	$data['heading_title'] = $this->language->get('heading_title');
		$this->load->model('catalog/category'); 
		$this->load->model('tool/image');

		$data['category'] = array();
		$data['test'] = array();

		$lang_id = (int)$this->config->get('config_language_id');

		$active_category = explode(',',$setting['active_category']);	

		$category_id = array();

		foreach ($active_category as $item) {
			
			preg_match($this->pattern, $item, $value);

			array_push($category_id,(int)substr($item, strlen($value[1]), strlen($item)));

		}

		foreach($category_id as $parent_item){

				$child_category = $this->model_catalog_category->getCategories($parent_item);

				$parent_name = $this->model_catalog_category->getCategory($parent_item);

				if ($parent_name['icon']) {
					if (!empty($this->config->get('theme_' . $this->config->get('config_theme') . '_icon_category_height'))) {
						$data['thumb'] = $this->model_tool_image->resize($parent_name['icon'], $this->config->get('theme_' . $this->config->get('config_theme') . '_icon_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_icon_category_height'));
					} else {
						$data['thumb'] = '';
					}
				} else {
					$data['thumb'] = '';
				}		

				// if ($parent_name['icon']) {					
				// 	$data['thumb'] = $this->model_tool_image->resize($parent_name['icon'], $this->config->get('theme_' . $this->config->get('config_theme') . '_icon_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_icon_category_height'));					
				// } else {
				// 	$data['thumb'] = '';
				// }

				if($child_category){
					$child_level_2 = array();
					foreach($child_category as $child_item){

						$child_child_category = $this->model_catalog_category->getCategories($child_item['category_id']);

						if($child_child_category){
							$child_level_3 = array();
							foreach($child_child_category as $child_child_item){												

								$child_child_child_category = $this->model_catalog_category->getCategories($child_child_item['category_id']);

								if($child_child_child_category){
									$child_level_4 = array();
									foreach($child_child_child_category as $child_child_child_item){

										$child_level_4[] = array(
											'name'  => $child_child_child_item['name'],
											'href'  => $this->url->link('product/category', 'path=' . $child_child_item['category_id'] . '_' . $child_child_child_item['category_id'])
										);									
									}

									$child_level_3[] = array(
										'name'  => $child_child_item['name'],
										'href'  => $this->url->link('product/category', 'path=' . $child_item['category_id'] . '_' . $child_child_item['category_id']),
										'level_4' => $child_level_4
									);
								}
								else{
									$child_level_3[] = array(
									'name'  => $child_child_item['name'],
									'href'  => $this->url->link('product/category', 'path=' . $child_item['category_id'] . '_' . $child_child_item['category_id'])

									);
								}							
							}

							$child_level_2[] = array(							
								'name'  => $child_item['name'],
								'href'  => $this->url->link('product/category', 'path=' . $parent_item . '_' . $child_item['category_id']),
								'level_3' => $child_level_3
							);

						}
						else{
							$child_level_2[] = array(							
							'name'  => $child_item['name'],
							'href'  => $this->url->link('product/category', 'path=' . $parent_item . '_' . $child_item['category_id'])
							);
						}
				 	}

				 	$data['category'][] = array(						
						'name'  => $parent_name['name'],
						'column' => $parent_name['column'],
						'href'  => $this->url->link('product/category', 'path=' . $parent_item),
						'level_2' => $child_level_2,
						'img'   => $data['thumb']
					);
					// print_r($data['category']);
				}
				else{
					$data['category'][] = array(						
						'name'  => $parent_name['name'],
						'column' => $parent_name['column'],
						'href'  => $this->url->link('product/category', 'path=' . $parent_item),
						'img'   => $data['thumb']
					);	

				}
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/mahardhi_category.twig')) {
            return $this->load->view('extension/module/mahardhi_category', $data);
        } else {
            return;
        }
	}
}
