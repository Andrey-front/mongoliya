<?php
class ModelMahardhiCategoryMenu extends Model {
	private $spacer_size = '3';
	private $html = '';
	private $pattern = '/^([a-z_]*)[0-9]+/';

	public function install() {		
		$hasModelChoiceField = FALSE;
		$result = $this->db->query( "DESCRIBE `".DB_PREFIX."category`;" );
		foreach ($result->rows as $row) {
		 if ($row['Field'] == 'icon') {
		    $hasModelChoiceField = TRUE;
		    break;
		 }
		}
		if (!$hasModelChoiceField) {
			$sql = "ALTER TABLE `".DB_PREFIX."category` ADD `icon` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `image`;";
			$this->db->query( $sql );
		}
	}

	public function uninstall() {		
		$sql = "ALTER TABLE `".DB_PREFIX."category` DROP `icon`";
	    $this->db->query( $sql );
	}

	public function getCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;
	}
	
	public  function getCategoryOption($id_category = 0, $id_lang = false, $id_shop = false, $recursive = true) {
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		//$ActiveCategory = explode(',',$this->config->get('active_category'));
		$category = $this->model_catalog_category->getCategory($id_category);
		
		if ($recursive){ 
			$children = $this->getCategories($id_category);	
			if(isset($category['top'])) {
				if($category['top'] ==0) {
					$category['top'] = 2;
				}
				$spacer = str_repeat('&nbsp;', $this->spacer_size * (int) $category['top']);
			}
		}	

		if(isset($category['category_id']) )
			$this->html .= '<option value="categoryid_'.(int)$category["category_id"].'">'.(isset($spacer) ? $spacer : '').$category["name"].' </option>';

	  if (isset($children) && count($children)){
			foreach ($children as $child){
				$this->getCategoryOption((int)$child['category_id']);
			}
	  }
					
	  return $this->html;		
		
	}
	
	public function getActiveMenu() {
		$data = $this->getMenu(); 
		$lang_id = (int)$this->config->get('config_language_id');
		$this->load->model('catalog/category');
		$this->load->model('catalog/information');
		$this->html = "";
		foreach ($data as $item)
		{
			if (!$item){
				continue;
			}

			preg_match($this->pattern, $item, $values);
			if(!isset($values[1])) {
				continue; 
			}
			$id = (int)substr($item, strlen($values[1]), strlen($item));

			switch (substr($item, 0, strlen($values[1])))
			{
				case 'categoryid_':			
					$category = $this->model_catalog_category->getCategory($id);//					
					if ($category){
						$this->html .= '<option value="categoryid_'.$id.'">'.$category['name'].'</option>'.PHP_EOL;
					}
					break;
				
				case 'cmsid_':						
					$cmspage = $this->model_catalog_information->getInformationDescriptions($id);					
					if($cmspage){ 
						$this->html .= '<option value="cmsid_'.$id.'">'.$cmspage[$lang_id]['title'].'</option>'.PHP_EOL;
					}
				break;
			}
		}
		return $this->html ;

	}
	
	private function getMenu(){
	
		$module_info = array();
		if (isset($this->request->get['module_id'])) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}else {
			
			$module_info['active_category'] = '';
		}	
		
		return explode(',',$module_info['active_category']);
	}
	
	
	public function getCmsOption()
	{
		$this->html = ""; 
		$this->load->model('catalog/information');
		$data = array(
			'sort'=> 'asc'
		);
		$results = $this->model_catalog_information->getInformations($data);
		$infomations = array();
		$url = null;
    	foreach ($results as $result) {
			$action = array();						
			$this->html .= '<option value="cmsid_'.$result['information_id'].'">'. $result['title'].'</option>'.PHP_EOL;	
		}		
		return $this->html; 
	}
}