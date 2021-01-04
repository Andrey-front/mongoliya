<?php

class ModelBoxberryZip extends Model
{
    public function getZip($zip)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_zips WHERE `zip` = '" . $this->db->escape($zip) . "'");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return null;
        }
    }

    public function getZips()
    {
        $data = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_zips ");
        foreach ($query->rows as $result) {
            $data[$result['zip']] = $result;
        }

        return $data;
    }

    public function addZip($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "boxberry_zips` SET 
        zip = '" . $this->db->escape($data['zip'])
            . "', expired = NOW() + INTERVAL 1 DAY"
            . " ON DUPLICATE KEY UPDATE 
            expired = NOW() + INTERVAL 1 DAY");
        $zip_id = $this->db->getLastId();

        return $zip_id;
    }
}