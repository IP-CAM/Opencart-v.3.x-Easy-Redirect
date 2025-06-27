<?php
class ModelExtensionModuleEasyRedirect extends Model {

	public function redirect(){
		
		if($this->config->get('easy_redirect_status') != 'on'){
			return;
		}

		$server = $this->request->server;
		
		$preserve_q = $this->db->query("SELECT * FROM `" . DB_PREFIX . "easy_redirect` WHERE from_url LIKE '%?%'");
		$request_uri = (strpos('%', $server['REQUEST_URI'])) ? urldecode($server['REQUEST_URI']) : $server['REQUEST_URI'];
		$url = (!$preserve_q->num_rows) ? explode('?', $request_uri) : array($request_uri);
		
		$from = 'http' . (!empty($server['HTTPS']) && $server['HTTPS'] != 'off' ? 's' : '') . '://' . $server['HTTP_HOST'] . $url[0];
		$from = strtolower($from);
		$from = str_replace('&amp;', '&', $from);
		if (substr($from, -1) == '/') $from = substr($from, 0, -1);

		$redirect_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "easy_redirect` WHERE '" . $this->db->escape($from) . "' = LCASE(from_url) OR '" . $this->db->escape($from) . "/' = LCASE(from_url)");

		if ($redirect_query->num_rows){
			$to = strtolower($redirect_query->row['to_url']);
			if (substr($to, -1) == '/'){
				$to = substr($to, 0, -1);	
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "easy_redirect` SET times_used = times_used + 1 WHERE id = " . (int)$redirect_query->row['id']);
			
			header('Location: ' . str_replace('&amp;', '&', $to), true, $redirect_query->row['response_code']);
			exit();
		}
	}
}