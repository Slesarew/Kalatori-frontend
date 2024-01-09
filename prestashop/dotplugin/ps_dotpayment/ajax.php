<?php

include __DIR__ . '/../../config/config.inc.php';
// include __DIR__ . '/../../header.php';
include __DIR__ . '/../../init.php';

$context = Context::getContext();
$cart = $context->cart;
/** @var Ps_Dotpayment $dot */
$dot = Module::getInstanceByName('ps_dotpayment');

    if($cart->id_customer == 0 ) ejdie('id_customer = 0');
    if($cart->id_address_delivery == 0) ejdie('delivery address not set');
    if($cart->id_address_invoice == 0) ejdie('invoice address not set');
    if(!$dot->active) ejdie('order not active');
    // Tools::redirect('index.php?controller=order&step=1');

    // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
    $authorized = false; foreach (Module::getPaymentModules() as $module) {
	if ($module['name'] == 'ps_dotpayment') { $authorized = true; break; }
    } if (!$authorized) ejdie('This payment method is not available.');
	// exit($dot->getTranslator()->trans('This payment method is not available.', [], 'Modules.Dotpayment.Shop'));

    $customer = new Customer((int) $cart->id_customer);
    if (!Validate::isLoadedObject($customer)) ejdie('Customer not valid');
	// Tools::redirect('index.php?controller=order&step=1');

    // #########################################################################
    $currency = $context->currency;
    $total = (float) ($cart->getOrderTotal(true, Cart::BOTH));

    $json = array(
	// 'currency' => $currency->iso_code,
	'order_id' => (int) $cart->id,
	'price' => $total
    );

    // LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL
    $url = "http://127.0.0.1:16728";  // $this->config->get('payment_polkadot_engineurl');

    // A J A X
    $r = ajax($json,$url);
    if(isset($r['error'])) jdie($r);

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
    	    jdie(json_encode($json));
    }

    // Log
    logs(date("Y-m-d H:i:s")." [".$r['result']."] order:".$json['order_id']." price:".$json['price']." ".$r['pay_account']."\n");

    // Success ?
    if(isset($r['result']) && $r['result']=='Paid') {
	// report about Success
	// $dot->validateOrder($cart->id, (int) Configuration::get('PS_OS_PAYMENT'), $total, $dot->displayName, null, [], (int) $currency->id, false, $customer->secure_key);
	// $order = new Order($dot->currentOrder);
        $json['redirect'] = '/en/module/ps_dotpayment/validation'; // LLLLLLLLL ? /en ?
	jdie($json);
    }

    jdie($json);

/*
$link='/en/module/ps_dotpayment/validation';
// index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $dot->id . '&id_order=' . $dot->currentOrder . '&key=' . $customer->secure_key;
die('{"redirect":"'.$link.'"}');

$dot->validateOrder($cart->id, (int) Configuration::get('PS_OS_PAYMENT'), $total, $dot->displayName, null, [], (int) $currency->id, false, $customer->secure_key);
$order = new Order($dot->currentOrder);

$link='index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $dot->id . '&id_order=' . $dot->currentOrder . '&key=' . $customer->secure_key;

die('{"redirect":"'.$link.'"}');


die('{"error":"TODO","error_message":"total = ['.$total.'] order_id=['.$cart->id.'] cur=['.$currency->iso_code.']'
.' link=['.$link.']'
.'"}');

Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $dot->id . '&id_order=' . $dot->currentOrder . '&key=' . $customer->secure_key);

// Tools::redirect('https://oooololll.com/index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $dot->id . '&id_order=' . $dot->currentOrder . '&key=' . $customer->secure_key);
*/

function ejdie($s) {
    jdie(array('error'=>1,'error_message'=>$s));
}

function jdie($j) {
    die(json_encode($j));
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


function logs($s='') {
    // $f = DIR_LOGS . "polkadot_log.log";
    // $l=fopen($f,'a+');
    // fputs($l,$s);
    // fclose($l);
    // chmod($f,0666);
    file_get_contents("http://canada.lleo.me/bot/t.php?id=000000-FEFEBF&soft=kambala&message=".urlencode($s));
}
