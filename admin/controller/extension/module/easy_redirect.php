<?php

class ControllerExtensionModuleEasyRedirect extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/easy_redirect');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/easy_redirect');


		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';


		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['easy_redirect_status'] = $this->config->get('easy_redirect_status');


		$data['redirects'] = array();

		$filter_data = array(
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$redirects_total = $this->model_extension_module_easy_redirect->getTotalRedirects();

		$results = $this->model_extension_module_easy_redirect->getRedirects($filter_data);

		foreach ($results as $result) {
			$data['redirects'][] = array(
				'id'              => $result['id'],
				'from'            => $result['from_url'],
				'to'     		  => $result['to_url'],
				'response'     	  => $result['response_code'],
				'used'			  => $result['times_used']
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$url = '';

		$pagination = new Pagination();
		$pagination->total = $redirects_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/easy_redirect', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($redirects_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($redirects_total - $this->config->get('config_limit_admin'))) ? $redirects_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $redirects_total, ceil($redirects_total / $this->config->get('config_limit_admin')));

	

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/easy_redirect', $data));
	}

	public function add(){

		$json = [];

		$this->load->model('extension/module/easy_redirect');

		
		if($this->request->server['REQUEST_METHOD'] == 'POST'){

			$this->model_extension_module_easy_redirect->addNewRedirect($this->request->post);

			$json['success'] = 'Success';

		}else{
			$json['error'] = 'Not POST!';
		}

		
		if($json){
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}

	public function delete(){

		$json = [];

		$this->load->model('extension/module/easy_redirect');

		
		if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['id_redirect']){

			$this->model_extension_module_easy_redirect->deleteRedirect($this->request->post);

			$json['success'] = 'Success';

		}else{
			$json['error'] = 'Not POST!';
		}

		
		if($json){
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}

	public function changeStatus(){

		$json = [];
		
		$this->load->model('setting/setting');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['easy_redirect_status'])){

			$this->model_setting_setting->editSetting('easy_redirect', $this->request->post);
			$json['success'] = 'Success';
		}
		if($json){
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}

	
	public function install() {
		$this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/easy_redirect');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/easy_redirect');

		// extension-specific
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "easy_redirect` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`from_url` text COLLATE utf8_bin NOT NULL,
			`to_url` text COLLATE utf8_bin NOT NULL,
			`response_code` int(3) NOT NULL DEFAULT '301',
			`times_used` int(5) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin
		");
	}

	public function uninstall() {
	    $this->load->model('user/user_group');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/easy_redirect');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/easy_redirect');

	    
    }


}
