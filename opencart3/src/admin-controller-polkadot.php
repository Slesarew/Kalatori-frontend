<?php
class ControllerExtensionPaymentPolkadot extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/polkadot');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_polkadot', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['error_warning'] = ( isset($this->error['warning']) ? $this->error['warning'] : '' );
		$data['error_merchant'] = ( isset($this->error['merchant']) ? $this->error['merchant'] : '' );
		$data['error_security'] = ( isset($this->error['security']) ? $this->error['security'] : '' );

		$data['breadcrumbs'] = array();
		    $data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		    );
		    $data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		    );
		    $data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/polkadot', 'user_token=' . $this->session->data['user_token'], true)
		    );

		$data['action'] = $this->url->link('extension/payment/polkadot', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		$a=array(
			'payment_polkadot_merchant',
			'payment_polkadot_security',
			'payment_polkadot_engineurl',
			'payment_polkadot_total',
			'payment_polkadot_order_status_id',
			'payment_polkadot_geo_zone_id',
			'payment_polkadot_status',
			'payment_polkadot_sort_order'
		);
		foreach($a as $l) $data[$l] = (isset($this->request->post[$l]) ? $this->request->post[$l] : $this->config->get($l) );

		$data['callback'] = HTTP_CATALOG . 'index.php?route=extension/payment/polkadot/callback';

		$this->load->model('localisation/order_status'); $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->load->model('localisation/geo_zone'); $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$a=array('header','column_left','footer');
		foreach($a as $l) $data[$l] = $this->load->controller('common/'.$l);

		$data['test_alive_url'] = HTTP_SERVER . 'index.php?route=extension/payment/polkadot/test_alive&user_token='.$this->session->data['user_token'];
		$this->response->setOutput($this->load->view('extension/payment/polkadot', $data));
	}

	protected function validate() {
	    if(!$this->user->hasPermission('modify', 'extension/payment/polkadot')) $this->error['warning'] = $this->language->get('error_permission');
	    $a=array('merchant','security','engineurl');
	    foreach($a as $l) { if(!$this->request->post['payment_polkadot_'.$l]) $this->error[$l] = $this->language->get('error_'.$l); }
	    return !$this->error;
	}

        public function test_alive(): void {
                // $url = $this->config->get('payment_polkadot_engineurl');
                $url=$this->request->post['engine_url'];
                $json=$this->test_url($url);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
        }

        function test_url($url) {
                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_POSTFIELDS => '{"order_id":0,"price":0}',
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

}