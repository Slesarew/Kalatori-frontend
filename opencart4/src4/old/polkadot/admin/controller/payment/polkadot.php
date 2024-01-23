<?php

namespace Opencart\Admin\Controller\Extension\Polkadot\Payment;

class Polkadot extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/polkadot/payment/polkadot');
		$this->document->setTitle($this->language->get('heading_title'));
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/polkadot/payment/polkadot', 'user_token=' . $this->session->data['user_token'])
		];
		$data['save'] = $this->url->link('extension/polkadot/payment/polkadot.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

////		$data['payment_polkadot_response'] = $this->config->get('payment_polkadot_response');
	$data['payment_polkadot_engineurl'] = $this->config->get('payment_polkadot_engineurl');

		$data['payment_polkadot_approved_status_id'] = $this->config->get('payment_polkadot_approved_status_id');
		$data['payment_polkadot_failed_status_id'] = $this->config->get('payment_polkadot_failed_status_id');
		$data['payment_polkadot_order_status_id'] = $this->config->get('payment_polkadot_order_status_id');
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['payment_polkadot_geo_zone_id'] = $this->config->get('payment_polkadot_geo_zone_id');
		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$data['payment_polkadot_status'] = $this->config->get('payment_polkadot_status');
		$data['payment_polkadot_sort_order'] = $this->config->get('payment_polkadot_sort_order');
//		$data['report'] = $this->getReport();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['test_alive_url']  = $this->url->link('extension/polkadot/payment/polkadot.test_alive', 'user_token=' . $this->session->data['user_token']);
	$this->response->setOutput($this->load->view('extension/polkadot/payment/polkadot', $data));
	}



	function test_url($url) {

	        $ch = curl_init();
	        curl_setopt_array($ch, array(
	            CURLOPT_POSTFIELDS => '{"order_id":0,"price":0}', // json_encode(array("order_id"=>0,"price"=>0)),
	            CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_FAILONERROR => true,
	            CURLOPT_CONNECTTIMEOUT => 2,
	            CURLOPT_TIMEOUT => 3,
	            CURLOPT_URL => $url
	        ));
	        $result = curl_exec($ch);
	        $errors = curl_error($ch);
	        curl_close($ch);

		$json=[];
		if($result) { $J=json_decode($result); if($J) $J=(array)$J; }
		if($result===false || !empty($errors) || !$J || !isset($J['version'])) {
		    $json['error']="Connection error ".htmlspecialchars($url);
		    $json['error_message']=print_r($errors,1);
		} else {
		    $json['version']=htmlspecialchars($J['version']);
		    $json['info']=$J;
		};
		$json['URL']=$url;
		$json['OUTPUT']=$result;
		$json['ERROR']=print_r($errors,1);
		return $json;
	}


        public function test_alive(): void {
                // $url = $this->config->get('payment_polkadot_engineurl');
		$url=$this->request->post['engine_url'];
		$json=$this->test_url($url);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		return;
        }

	public function save(): void {
		$this->load->language('extension/polkadot/payment/polkadot');
		$json = [];
		if (!$this->user->hasPermission('modify', 'extension/polkadot/payment/polkadot')) {
			$json['error'] = $this->language->get('error_permission');
                        // $this->error['warning'] = $this->language->get('error_permission');
		}

		$url=$this->request->post['payment_polkadot_engineurl'];
		if(empty($url)) {
		    $json['error'] = $this->language->get('error_daemon'); // "Empty daemon url"; // 
		} else {
		    $r=$this->test_url($url);
		    if(!isset($r['version'])) $json['error'] = "Error daemon ".htmlspecialchars($url); // $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('payment_polkadot', $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		if ($this->user->hasPermission('modify', 'extension/payment')) {
			$this->load->model('extension/polkadot/payment/polkadot');
			$this->model_extension_polkadot_payment_polkadot->install();
		}
	}
	public function uninstall(): void {
		if ($this->user->hasPermission('modify', 'extension/payment')) {
			$this->load->model('extension/polkadot/payment/polkadot');
			$this->model_extension_polkadot_payment_polkadot->uninstall();
		}
	}
}
