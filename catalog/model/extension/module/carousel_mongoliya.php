<?php
class ModelExtensionModuleCarouselMongoliya extends Model {
  
  private $table = DB_PREFIX .'carousel_mongoliya';

  public function getBanners() {
		
		$sql = "SELECT id, photo, caption, msg, link, link_text
      FROM " . $this->table ." ORDER BY id DESC";
    
		return $this->db->query($sql)->rows;

  }
}