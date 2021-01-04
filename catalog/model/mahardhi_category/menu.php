<?php
class ModelMahardhiCategoryMenu extends Model {	
    private $_show_level = 4;
	private $_menuLink = '';
	private $pattern = '/^([a-z_]*)[0-9]+/';
	
	public function getMenuCustomerLink($lang_id = NULL,$setting = array()) {
		$menu_items = $this->getMenuItems($setting);
		$item1=0;
		$lang_id = (int)$this->config->get('config_language_id');
		//$id_shop = (int)Shop::getContextShopID();
		$showhome= $setting['homelink'];
		//$showhome= true;
        //$item_number = $setting['hitem_limit'];
        $item_number = 3;
        if ($showhome) {
			$act = '';

            if (isset($this->request->get['route'])) {
                $route = (string)$this->request->get['route'];
            } 
            elseif (isset($this->request->get['route']) == 'common/home') {
                $act = ' active';
             }
            else {
                $act = 'active';
            }

			
			$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $id = "_home";
            //$this->_menuLink .= '<div id="pt_ver_menu' . $id . '" class="pt_ver_menu' . $act . '">';
            //$this->_menuLink .= '<div class="parentMenu">';
            $this->_menuLink .= '<li class="'. $act . '">';
            $this->_menuLink .= '<a href="'.$url.'">';
            $this->_menuLink .= $this->language->get("text_home");
            $this->_menuLink .= '</a>';
            $this->_menuLink .= '</li>';
            //$this->_menuLink .= '</div>';
            //$this->_menuLink .= '</div>';
        }
		
		$count = 0;
        $setting['is_over'] = false;
        $over_class = "";

		foreach ($menu_items as $item)
		{
			if (!$item)
				continue;

			preg_match($this->pattern, $item, $value);
			$id = (int)substr($item, strlen($value[1]), strlen($item));

            if($count >= $item_number) {
                $over_class = ' over-menu';
                $setting['is_over'] = true;
            }

			switch (substr($item, 0, strlen($value[1])))
			{
				case 'categoryid_':
					$item1 = $item1+ 1; 
					$this->_menuLink .= $this->drawCustomMenuItem($id, 0, false, $item1, $lang_id,$setting);
					break;
				case 'LINK': 
					$link = $this->getTopLinks($id);
                    if($link) {
                        $link = $link[0];
                        //echo "<pre>"; print_r($cms); echo "</pre>";
                        $this->_menuLink .= '<div id ="pt_ver_menu_link" class ="pt_ver_menu '. $over_class .'"><div class="parentMenu" ><a href="'.($link['link']).'"><span>'.$link['title'].'</span></a></div></div>'.PHP_EOL;
                    }
				// case 'CMS': 
				// 	$this->load->model('catalog/information');
				// 	$cms = $this->model_catalog_information->getInformation($id);
				// 	//echo "<pre>"; print_r($cms); echo "</pre>";
    //                 if(isset($cms['information_id'])) {
    //                     $cms_link = $this->url->link('information/information', 'information_id=' . $cms['information_id']);
    //                     $this->_menuLink .= '<div id ="pt_ver_menu_cms" class ="pt_ver_menu '. $over_class .'"><div class="parentMenu" ><a href="'.$cms_link.'"><span>'.$cms['title'].'</span></a></div></div>'.PHP_EOL;
    //                 }
			}
            $count++;        
		}
		
        $this->load->language('extension/module/mahardhi_category');

         // block customer menu link
         // $blockCustomer = $this->getCmsBlockContent(null, 'item');
         // foreach ($blockCustomer as $bc) {
         //     if($bc['status']) {
         //         $this->_menuLink .= $this->drawCustomMenuBlock($bc['identify'], $bc, $setting);
         //     }
         // }

        // if($setting['is_over']) {
        //     $this->_menuLink .= '<div class="more-wrap pt_ver_menu"><div class="parentMenu"><a href="javascript:void(0);" class="more-ver-menu"><span>'.$this->language->get('text_more_vertical_menu').'</span></a></div></div>';
        //     $this->_menuLink .= '<div class="close-wrap pt_ver_menu over-menu"><div class="parentMenu"><a href="javascript:void(0);" class="close-ver-menu"><span>'.$this->language->get('text_close_vertical_menu').'</span></a></div></div>';
        // }

		return 	$this->_menuLink ; 

	}

	private function getMenuItems($setting = array()){
		//echo "<pre>"; print_r($setting) ; echo "</pre>";
	//	$this->getInfomatinOptions();
		return explode(',',$setting['active_category']);
	}

	public function drawCustomMenuItem($category, $level = 0, $last = false, $item, $lang_id,$setting = array()) {
        //$setting['hlevel'] = 4;
        if($setting['status']) {
            $this->_show_level = $setting['levels'];
        } else {
            $this->_show_level = 4;
        }
        //$cateCurrent = $this->getCurrentCategoriesId($lang_id);

        $html = array();
        $blockHtml = '';
        $id_shop = 1;
        $id = $category;
        $blockId = sprintf('pt_ver_menu_idcat_%d', $id);
        //$staticBlock = $this->getCmsBlockContent($blockId);
        //$blockIdRight = sprintf('pt_ver_menu_idcat_%d_right', $id);
        //$staticBlockRight = $this->getCmsBlockContent($blockIdRight);
        // --- Static Block ---
       // $blockHtml = $staticBlock;
        /* check block right */
        //$blockHtmlRight = $staticBlockRight;

        //if ($blockHtmlRight)
           // $blockHtml = $blockHtmlRight;
        // --- Sub Categories ---
        $activeChildren = $this->model_catalog_category->getCategories($id);
       // print_r($activeChildren);		
        $activeChildren = $this->getCategoryByLevelMax($activeChildren);
        // --- class for active category ---
		//echo "<pre>"; print_r($_GET	); 
        $active = '';
		$category_id = $this->getCurrentCatetgoryID($this); 
		if($category_id == $id) $active = ' act'; 
        //if (isset($cateCurrent[0]) && in_array($category, array($cateCurrent[0])))
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        
        $over_class = '';
        if($setting['is_over']) {
            $over_class = ' over-menu';
        }
        if ($drawPopup) {
            $html[] = '<li id="pt_ver_menu' . $id . '" class="pt_ver_menu' . $active . $over_class .' nav-' . $item . ' had-child">';
        } else {
            $html[] = '<li id="pt_ver_menu' . $id . '" class="pt_ver_menu' . $active . $over_class .' nav-' . $item . ' pt_ver_menu_no_child">';
        }
		//echo $category;
        //$cate = new Category((int) $category);
		$cate = $this->model_catalog_category->getCategory($id);
        //$link = $categoryObject->getLinkRewrite($category, $lang_id);
        $parameters = "";
		$link = $this->url->link('product/category', "path=".$id);
		
		$name = ""; 
		if(isset($cate['name'])) {
        // --- Top Menu Item ---
       // $html[] = '<div class="parentMenu">';
        $html[] = '<a href="' . $link . '">';
			$name = strip_tags($cate['name']);
			$name = $name;
			// if(isset($cate['thumbnail_image'])) {
				// $thumbnail_image =  $this->model_tool_image->resize($cate['thumbnail_image'], 22,22);
				// $html[] = '<img src="'.$thumbnail_image.'"  alt ="thumbnail_image" />';
			// }
			$html[] = $name;
			$html[] = '</a>';
			//$html[] = '</div>';
		}
		    // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            if ($this->_show_level >= 2) {
                // --- Popup function for hide ---
                $html[] = '<div id="popup' . $id . '" class="popup">';
                $html[] = '<div class="content-popup">';
                //$html[] = '<div class="arrow-left-menu"></div>';
                // --- draw Sub Categories ---
                 if (count($activeChildren) || $blockHtml) {
					//echo "<pre>"; print_r($blockHtml); echo "</pre>"; 
                    $html[] = '<ul class="block1" id="block1' . $id . '">';
                    $html[] = $this->drawColumns($activeChildren, $id, $lang_id,$setting);
                    // if ($blockHtml && $blockHtmlRight) { 
                    //     $html[] = '<div class="column blockright">';
                    //     $html[] = $blockHtml;
                    //     $html[] = '</div>';
                    // }
                    //$html[] = '<div class="clearBoth"></div>';
                    $html[] = '</ul>';
                }
                // --- draw Custom User Block ---
                // if ($blockHtml && !$blockHtmlRight) { 
                //     $html[] = '<div class="block2" id="block2' . $id . '">';
                //     $html[] = $blockHtml;
                //     $html[] = '</div>';
                // }
                $html[] = '</div>';
                $html[] = '</div>';
            }
        }

 
        $html[] = '</li>';
		//echo "<pre>"; print_r($html); echo "</pre>";
        $html = implode("\n", $html);
        return $html;
    }


    public function getCategoryByLevelMax($cates = NULL) {
        if (count($cates) < 1)
            return array();
        $cateArray = array();
        foreach ($cates as $key => $cate) {
            $cate_id = $cate['category_id'];
			$cateObject = $this->model_catalog_category->getCategory((int) $cate_id);
            $cate_level = $cateObject['top'];
           
                $cateArray[$key] = $cate;
            
        }

        if ($cateArray)
            return $cateArray;
        return array();
    }

     public function getCurrentCategoriesId($lang_id = NULL) {
        if (isset($_GET['category_id'])) {
            $lastCateId = $_GET['category_id'];
        } else {
            $lastCateId = 0;
        }

        $lastCate = $this->model_catalog_category->getCategory((int) $lastCateId);
        //echo $lastCate->name[1]; echo '--------';
        $parentCate = $lastCate->getParentsCategories($lang_id);
        $arrayCateCurrent = array();
        foreach ($parentCate as $pcate) {
            $arrayCateCurrent[] = $pcate['id_category'];
        }
        return $arrayCateCurrent;
    }
    function getCurrentCatetgoryID($obj){    
	if(isset($obj->request->get['path'])) {
		$path = $obj->request->get['path'];
		$cats = explode('_', $path);
		$cat_id = $cats[count($cats) - 1];
		return $path;
		} else {
		return null;
		}
	}

	public function drawColumns($children, $id, $lang_id,$setting = array()) {
			$html = '';
			//$setting['hdepth'] = '3';
			// --- explode by columns ---
			if(isset($setting)) {
				$columns =$setting['columns'];
			} else {
				$columns =4;
			}
			
			if ($columns < 1)
				$columns = 1;
			$chunks = $this->seperateColumns($children, $columns, $lang_id);
			$columChunk = count($chunks);
			// --- draw columns ---
			$classSpecial = '';
			$keyLast = 0;
			foreach ($chunks as $key => $value) {
				if (count($value))
					$keyLast++;
			}
			$blockHtml = '';
			//$id_shop = (int) Context::getContext()->shop->id;
			$blockId = sprintf('pt_ver_menu_idcat_%d', $id);
			//$staticBlock = $this->getCmsBlockContent($blockId);
			//$blockIdRight = sprintf('pt_ver_menu_idcat_%d_right', $id);
			//$staticBlockRight = $this->getCmsBlockContent($blockIdRight);
			// --- Static Block ---
			//$blockHtml = $staticBlock;
			/* check block right */
			//$blockHtmlRight = $staticBlockRight;

			foreach ($chunks as $key => $value) {
				   if (!count($value))
						continue;
					if ($key == 0) {
						$classSpecial = ' first';
					} else {
						$classSpecial = '';
					}
			
				//$html.= '<li class="column' . $classSpecial . ' col' . ($key + 1) . '">';
				$html.= $this->drawMenuItem($value, 1, $columChunk, $lang_id,$setting);

				//$html.= '</li>';
			}
			return $html;
		}

		 public function seperateColumns($parentCates, $num, $lang_id) {
			$countChildren = 0;
			foreach ($parentCates as $cat => $childCat) {
			         $activeChildCat = $this->model_catalog_category->getCategories($childCat['category_id']);		
					$activeChildCat = $this->getCategoryByLevelMax($activeChildCat);
				if ($activeChildCat) {
					$countChildren++;
				}
			}
			if ($countChildren == 0) {
				$num = 1;
			}


			$count = count($parentCates);
			if ($count)
				$parentCates = $this->partition_element($parentCates,$num);

        return $parentCates;
    }

    function partition_element(Array $list, $p) {
		$listlen = count($list);
		$partlen = floor($listlen / $p);
		$partrem = $listlen % $p;
		$partition = array();
		$mark = 0;
		for($px = 0; $px < $p; $px ++) {
			$incr = ($px < $partrem) ? $partlen + 1 : $partlen;
			$partition[$px] = array_slice($list, $mark, $incr);
			$mark += $incr;
		}
		return $partition;
	}

	public function drawMenuItem($children, $level = 1, $columChunk = 0, $lang_id = 1,$setting) {
		$lang_id = (int)$this->config->get('config_language_id');
		$this->load->model('catalog/category');
		
        //$html = '<div class="itemMenu level' . $level . '">';
        $html = '';
     
        $countChildren = 0;
        $ClassNoChildren = '';

        foreach ($children as $child) {
            $activeChildCat = $this->model_catalog_category->getCategories($child['category_id']);		
            $activeChildCat = $this->getCategoryByLevelMax($activeChildCat);
            if ($activeChildCat) {
                $countChildren++;
            }
        }
        if ($countChildren == 0 && $columChunk == 1) {
            $ClassNoChildren = ' nochild';
        }

        foreach ($children as $child) {
             $info =  $this->model_catalog_category->getCategory($child['category_id']);
            $level = (int)  $this->getCategoryLevelByCateId($child['category_id']);
            $active = '';
            //$currentCate = $this->getCurrentCategoriesId($lang_id);
            $cate_id = (int) $child['category_id'];
          //  if (in_array($cate_id, $currentCate)) {
                if ($this->haveCateChildren($cate_id, $lang_id)) {
                    $active = ' actParent';
                } else {
                    $active = ' act';
                }
            //}
            // --- format category name ---
            $name = strip_tags($child['name']);
            $name = str_replace(' ', '&nbsp;', $name);

            if (count($child) > 0) {
                $parameters = null;
                $link = $this->url->link('product/category', "path=".$child['category_id']);
         
                $html.= '<li><a class="itemMenuName level' . $level . $active . $ClassNoChildren . '" href="' . $link . '">' . $name . '</a>';

                if($setting['levels'] > 2) {
                    $activeChildren = $this->model_catalog_category->getCategories($child['category_id']);
                    $activeChildren = $this->getCategoryByLevelMax($activeChildren);
                    if (count($activeChildren) > 0) {
                        $html.= '<ul class="itemSubMenu level' . $level . '">';
                        //$html.= $this->drawMenuItem($activeChildren, $level + 1);
                        $html.= $this->drawMenuItem($activeChildren, $level + 1,$columChunk, $lang_id, $setting);
                        $html.= '</ul>';
                    }
                }
                $html .='</li>';

            }
        }
       // $html.= '</div>';
        return $html;
    }

    public function getCategoryLevelByCateId($category_id=null) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)  LEFT JOIN " . DB_PREFIX . "category_path cp ON (c.category_id = cp.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE  cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = 1 AND c.`category_id`='".$category_id."' ORDER BY c.sort_order");
		$results = $query->rows;
		foreach($results as $result ) {
			$cate_level = $result['level']; 
		}
		if($cate_level) return $cate_level ;
		return 0; 
	}

	public function haveCateChildren($cate_id = NULL, $lang_id = NULL) {
       	$childCates =   $this->model_catalog_category->getCategories($cate_id);	
        if (count($childCates) > 0)
            return true;
        return false;
    }
	
}
?>