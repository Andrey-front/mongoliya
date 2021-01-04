<?php
class ControllerExtensionModuleMahardhiFeatureCategory extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/mahardhi_feature_category');

		$data['heading_title'] = $this->language->get('heading_title');


		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['categories'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['category'])) {
			$categories = array_slice($setting['category'], 0, (int)$setting['limit']);

			foreach ($categories as $category_id) {
				$category_info = $this->model_catalog_category->getCategory($category_id);
				$children_data = array();

				if ($category_info) {

					$children = $this->model_catalog_category->getCategories($category_info['category_id']);
					foreach($children as $child) {
						$filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

						$children_data[] = array(
							'category_id' => $child['category_id'],
							'name' => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
							'href' => $this->url->link('product/category', 'path=' . $category_info['category_id'] . '_' . $child['category_id'])
						);
					}

					if ($category_info['banner']) {
						$image = $this->model_tool_image->resize($category_info['banner'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					$data['categories'][] = array(
						'category_id'  => $category_info['category_id'],
						'thumb'       => $image,
						'name'        => $category_info['name'],
						'children'    => $children_data,
						'description' => utf8_substr(strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
						'href'        => $this->url->link('product/category', 'path=' . $category_info['category_id'])
					);
				}
			}
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/mahardhi_feature_category.twig')) {
      return $this->load->view('extension/module/mahardhi_feature_category', $data);
  	} else {
	    return;
  	}
	}
}
