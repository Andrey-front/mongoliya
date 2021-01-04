<?php
class ModelExtensionModuleFeedback extends Model {

  public function getSettings(){
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE code = 'feedback'");
		
		if ($query->row) {
			return json_decode($query->row['setting'], true);
		} else {
			return array();	
		}
  }

}