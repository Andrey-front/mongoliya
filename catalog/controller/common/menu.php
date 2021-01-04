<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		// Blog Enable
		$results = $this->model_setting_extension->getExtensions('module');
		foreach($results as $result){
			if ($result['code'] === 'blog') {
				$data['enableBlog'] = true;
			}
		}
		$data['blogs_link'] = $this->url->link('blog/blog');


		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['special'] = $this->url->link('product/special');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					//level 3
					$children_data_3 = array();

					$children_3 = $this->model_catalog_category->getCategories($child['category_id']);
					foreach ($children_3 as $child_3) {
						$filter_data_3 = array(
							'filter_category_id'  => $child_3['category_id'],
							'filter_sub_category' => true
						);
						$children_data_3[] = array(
							'name'  => $child_3['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data_3) . ')' : ''),
							'href'  => $this->url->link('product/category', 'path=' . $child['category_id'] . '_' . $child_3['category_id'])
						);
					}
					// end lavel 3
					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);
					$children_data[] = array(
						'name'         => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href'         => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
						'grand_childs' => $children_data_3
					);
				}

				// Category Image
				$thumb='';
				if (isset($category['menuimage'])) {
					$children = $this->model_catalog_category->getCategories($category['category_id']);
					foreach ($children as $child) {
						$filter_data = array(
							'filter_category_id'  => $child['category_id']
						);
						$this->load->model('tool/image');
						$image = empty($category['image']) ? 'no_image.jpg' : $category['image'];
						$thumb = $this->model_tool_image->resize($image, 848, 160, $filter_data);
					}
				}

				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'thumb'    => $thumb,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}
		return $this->load->view('common/menu', $data);
	}
}
