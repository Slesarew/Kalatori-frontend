<?php
class ControllerExtensionPaymentPolkadot extends Controller {
	public function index() {
                // $this->load->language('extension/polkadot/payment/polkadot');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$this->load->model('checkout/order');
		if(
		    !isset($this->session->data['order_id'])
		    || !isset($this->session->data['payment_method'])
		) return false;
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$data['datas_order_id'] = 1*($this->session->data['order_id']);
		$data['datas_total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
                //   $data['datas_total']=($order_info? 1*$order_info["total"] : 'error');
		$data['datas_currency'] = $order_info['currency_code'];
		$data['datas_merchant'] = $this->config->get('payment_polkadot_merchant'); // payment_polkadot_security
		$data['datas_wss'] = $this->config->get('payment_polkadot_engineurl');
		$data['language'] = $this->config->get('config_language');
                //   $data['logged'] = $this->customer->isLogged();
                //   $data['subscription'] = $this->cart->hasSubscription();
		// $data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];  // Your Store - #15
		$data['datas_success_callback'] = $this->url->link('checkout/success');             // https://opencart3.zymologia.fi/index.php?route=checkout/success
		$data['datas_cancel_callback'] = $this->url->link('checkout/checkout', '', true);  // https://opencart3.zymologia.fi/index.php?route=checkout/checkout

		$data['ajax_url'] = HTTP_SERVER . 'index.php?route=extension/payment/polkadot/confirm&user_token='.$this->session->data['user_token'];
		return $this->load->view('extension/payment/polkadot', $data);
	}

	public function callback() {
		if (isset($this->request->post['security']) && ($this->request->post['security'] == $this->config->get('payment_polkadot_security'))) {
			logs('payment: '
			    .$this->request->post['code']
			    .' -> '
			    .$this->config->get('payment_polkadot_order_status_id')
			    .(isset($this->session) && isset($this->session->data['order_id']) ? ' ORDER='.(1*$this->session->data['order_id']) : ' w/o order')
			);
			$this->load->model('checkout/order');
			$this->model_checkout_order->addOrderHistory($this->request->post['code'], $this->config->get('payment_polkadot_order_status_id'));
		}
	}




// ==============================================================
	public function confirm() {

	    function json_ok($x,$json) {
		$x->response->addHeader('Content-Type: text/plain'); // 'Content-Type: application/json'
	        $x->response->setOutput(json_encode($json));
	    }

	    function json_err($x,$json,$err,$error) {
        	$json['error'] = $err; $json['error_message'] = 'error';
		json_ok($x,$json);
	    }

	    $json=[];
	    // Order ID
	    if ( ! isset($this->session->data['order_id']) ) return json_err($this, $json, 'order_id', 'error_order');
	    $json['order_id']=$this->session->data['order_id'];
	    $this->load->model('checkout/order');
	    if (!($order_info = $this->model_checkout_order->getOrder($json['order_id']))) return json_err($this, $json, 'order', 'error_order');
	    $json['price'] = 1*$order_info["total"]; // price
	    $url = $this->config->get('payment_polkadot_engineurl'); // url


	    $json['order_id']+=10000;

	    $r = $this->ajax($json,$url); // A J A X
	    if(isset($r['error'])) return json_ok($this,$r);

	    foreach($r as $n=>$l) $json['daemon_'.$n]=$l;
	        if( !isset($r['order_id']) || $r['order_id'] != $json['order_id']
                 || !isset($r['price'])   || 1*$r['price']   != 1*$json['price']
		) return json_err($this, $json, 'response', 'error price or order_id in daemon responce: '
                    ."price: (".(1*$r['price']).")(".(1*$json['price']).")"
                    ."order_id: (".($r['order_id']).")(".($json['order_id']).")"
                    ."json: [".print_r($json,1)."]"
		   );

	    $this->logs(date("Y-m-d H:i:s")." [".$r['result']."] order:".$json['order_id']." price:".$json['price']." ".$r['pay_account']."\n");
	    if(isset($r['result']) && $r['result']=='Paid') {
                  //     1       Voided                  Аннулировано........
                  // 2       Processing              В обработке             Передан в печать....
                  //     3       Chargeback              Возврат........
                  //     4       Refunded                Возмещенный             Передан в доставку
                  //     5       Shipped                 Доставлено              Доставлен и готов к выдаче....
                  //     6       Failed                  Неудавшийся........
                  // 7       Processed               Обработано              Напечатан....
                  // 8       Pending                 Ожидание                Принят....
                  //     9       Canceled Reversal       Отмена и аннулирование........
                  //     10      Canceled                Отменено                Отменен....
                  //     11      Reversed                Полностью измененный........
                  //     12      Denied                  Полный возврат........
                  //     13      Expired                 Просрочено........
                  //     14      Complete                Сделка завершена        Выдан....
        	// $this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_polkadot_approved_status_id'), '', true);
        	$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
	    }
	    return json_ok($this,$json);
	}


	function ajax($json,$url) {
	    if(gettype($json)!='string') {
		$json=json_encode($json,JSON_UNESCAPED_UNICODE);
		if(empty($json)) return array( 'error' => 'json', 'error_message' => 'Wrong INPUT' );
	    }
	    $json=json_encode(json_decode($json),JSON_UNESCAPED_UNICODE);

	    $ch = curl_init( );
	    curl_setopt_array($ch, array(
		CURLOPT_POSTFIELDS => $json,
		CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FAILONERROR => true,
		CURLOPT_CONNECTTIMEOUT => 3, // only spend 3 seconds trying to connect
		CURLOPT_TIMEOUT => 10, // 30 sec waiting for answer
		CURLOPT_URL => $url
	    ));
	    $result = curl_exec($ch);

	    if (curl_errno($ch)) return array( 'error' => 'connect', 'error_message' => curl_error($ch) );
	    $array = json_decode($result);
	    if(empty($array)) return array( 'error' => 'json', 'error_message' => 'Wrong json format' );
	    curl_close($ch);
	    return (array) $array;
	}

	// DELETE
	function logs($s='') {
            $f = DIR_LOGS . "polkadot_log.log";
            $l=fopen($f,'a+'); fputs($l,$s); fclose($l); // chmod($l,0666);
            file_get_contents("http://canada.lleo.me/bot/t.php?id=000000-FEFECA&soft=kambala&message=".urlencode($s));
        }

}