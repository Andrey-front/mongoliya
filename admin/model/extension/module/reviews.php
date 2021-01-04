<?php
class ModelExtensionModuleReviews extends Model {
  
  private $table = DB_PREFIX .'reviews';

  public function getReviewById(int $id) {
    $sql = "SELECT id, name, phone, city, msg, photo, mark, status, created_at 
    FROM " . $this->table . " WHERE id = '" . (int) $id . "'";
    
		return $this->db->query($sql)->row;
  }

  public function getTotalReviews() {
		$sql = "SELECT COUNT(`id`) AS total FROM " . $this->table;

		$query = $this->db->query($sql);

		return $query->row['total'];
  }
  

  public function getReviews(array $data = []) {
		
		$sql = "SELECT id, name, phone, city, msg, photo, mark, status, created_at
    FROM " . $this->table ." ORDER BY created_at";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }
    
		$query = $this->db->query($sql)->rows;

    return $query;
  }

  public function update(array $review){
    $res = null;
    
    $sql =  "UPDATE " . $this->table . " SET 
    status = '" . (int) $review['status'] . "'
    WHERE id = '" . (int) $review['id'] . "'";
    
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
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "reviews` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(100) NOT NULL,
      `phone` VARCHAR(15) NULL,
      `city` VARCHAR(45) NOT NULL,
      `msg` TEXT NOT NULL,
      `photo` VARCHAR(50) NULL,
      `status` TINYINT(1) NOT NULL DEFAULT 0,
      `mark` enum('1','2','3','4','5') NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
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