<?php
class ModelExtensionModuleEasyRedirect extends Model {

	public function getTotalRedirects() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "easy_redirect");

		return $query->row['total'];
	}
	
	public function getRedirects($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "easy_redirect";

		if (!empty($data['filter_from'])) {
			$sql .= " WHERE from LIKE '" . $this->db->escape($data['filter_from']) . "%'";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function addNewRedirect($data){
		if($data['id_redirect'] == ''){

			$this->db->query("INSERT INTO `" . DB_PREFIX. "easy_redirect` SET from_url = '" . $this->db->escape($data['from_url']) . "', to_url = '" . $this->db->escape(trim($data['to_url'])) . "', response_code = " . (int)$data['response']);
		}else{

			$this->db->query("UPDATE `" . DB_PREFIX. "easy_redirect` SET from_url = '" . $this->db->escape($data['from_url']) . "', to_url = '" . $this->db->escape(trim($data['to_url'])) . "', response_code = " . (int)$data['response'] ." WHERE id = " . (int)$data['id_redirect']);
		}
	}

	public function deleteRedirect($data){

		$this->db->query("DELETE FROM `" . DB_PREFIX. "easy_redirect` WHERE id = " . (int)$data['id_redirect']);
	}


}