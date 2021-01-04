<?php
class ModelExtensionModuleCarouselMongoliya  extends Model {
  
  private $table = DB_PREFIX .'carousel_mongoliya';

  public function getBannerById(int $id) {
    $sql = "SELECT id, photo, caption, msg, link, link_text
    FROM " . $this->table . " WHERE id = '" . (int) $id . "'";
    
		return $this->db->query($sql)->row;
  }

  public function getBanners() {
		
		$sql = "SELECT id, photo, caption, msg, link, link_text
    FROM " . $this->table ." ORDER BY id DESC";
    
		$query = $this->db->query($sql)->rows;

    return $query;
  }

  public function save(array $banner){

    $this->db->query("INSERT INTO " . $this->table . " SET 
      caption = '" . $this->db->escape($banner['caption']) . "',
      msg = '" . $this->db->escape($banner['msg']) . "',
      link = '" . $this->db->escape($banner['link']) . "',
      link_text = '" . $this->db->escape($banner['link_text']) . "',
      photo = '" . $this->db->escape($banner['photo']) . "'
      ");

    return $this->db->getLastId();
  }

  public function update(array $banner){
    $res = null;
    
    $sql =  "UPDATE " . $this->table . " SET 
    caption = '" . $this->db->escape($banner['caption']) . "',
    msg = '" . $this->db->escape($banner['msg']) . "',
    link_text = '" . $this->db->escape($banner['link_text']) . "',
    link = '" . $this->db->escape($banner['link']) . "'";
    
    if($banner['photo']){
      $sql .= ", photo = '" . $this->db->escape($banner['photo']) . "'";
    }

    $sql .= " WHERE id = '" . (int) $banner['id'] . "'";
    
    $res = $this->db->query($sql);

    return $res;
  }

  public function delete(int $id){
    $res = null;

    if($id > 0){
      $res =  $this->db->query("DELETE FROM " . $this->table . " WHERE id = '" . (int) $id . "'");;
    }

    return $res;
  }

  public function install() {
    $this->db->query("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "carousel_mongoliya` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `photo` VARCHAR(50) NULL,
      `caption` VARCHAR(256 ) NOT NULL,
      `msg` TEXT NOT NULL,
      `link` VARCHAR(256) NOT NULL,
      `link_text` VARCHAR(256) NOT NULL,
      PRIMARY KEY (`id`))
    ");
  }

  public function uninstall() {
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "reviews`;");
  }

  private function isEmptyTable($table): int{
		return $this->db->query("select exists(select 1 from ".DB_PREFIX.$table.") as output")->row['output'];
  }

	private function isTableExists($table){
		return $this->db->query("SELECT EXISTS(
			SELECT * FROM information_schema.tables 
			WHERE table_schema = '".$this->getCurrentDbName()."' 
			AND table_name = '".DB_PREFIX.$table."'
		) as output")->row['output'];
  }

}