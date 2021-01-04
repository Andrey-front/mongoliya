<?php
class ModelExtensionModuleReviews extends Model {
  
  private $table = DB_PREFIX .'reviews';

  public function saveReciew(array $review){

    $this->db->query("INSERT INTO " . $this->table . " SET 
      name = '" . $this->db->escape($review['name']) . "',
      phone = '" . $this->db->escape($review['phone']) . "',
      city = '" . $this->db->escape($review['city']) . "',
      msg = '" . $this->db->escape($review['msg']) . "',
      photo = '" . $this->db->escape($review['photo']) . "',
      mark = '" . $this->db->escape($review['mark']) . "'
      ");

    return $this->db->getLastId();
  }
  
  public function getTotalReviews() {
		$sql = "SELECT COUNT(`id`) AS total FROM " . $this->table." WHERE status = 1";

		$query = $this->db->query($sql);

		return $query->row['total'];
  }
  

  public function getReviews(array $data = []) {
		
		$sql = "SELECT id, name, phone, city, msg, photo, status, mark, created_at
    FROM " . $this->table ." WHERE status = 1 ORDER BY created_at DESC";

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
}