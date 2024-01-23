<?php
// namespace Opencart\Catalog\Model\Extension\OcPaymentExample\Payment;
namespace Opencart\Catalog\Model\Extension\Polkadot\Payment;

class Polkadot extends \Opencart\System\Engine\Model {

	public function getMethods(array $address): array {

		// error_log("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@");

		$this->load->language('extension/polkadot/payment/polkadot');
		if (!$this->config->get('config_checkout_payment_address')) {
			$status = true;
		} elseif (!$this->config->get('payment_polkadot_geo_zone_id')) {
			$status = true;
		} else {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_polkadot_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");
			if ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
		}

		$method_data = [];

                if ($status) {
			$option_data = [];
                        $option_data['polkadot'] = [
                                'code' => 'polkadot.polkadot',
                                'name' => $this->language->get('text_card_use') // $this->config->get('payment_polkadot_display_name')
                        ];

                        $method_data = array(
                                'code' => 'polkadot',
                                'name' => $this->language->get('heading_title'), // $this->config->get('payment_polkadot_display_name'),
                                'option' => $option_data,
                                'sort_order' => $this->config->get('payment_polkadot_sort_order')
                        );
                }





/*

		if ($status) {
			$option_data = [];
			$option_data['polkadot'] = [
				'code' => 'polkadot.polkadot',
				'name' => $this->language->get('text_card_use')
			];
			$results = $this->getPolkadots($this->customer->getId());
			foreach ($results as $result) {
				$option_data[$result['polkadot_id']] = [
					'code' => 'polkadot.' . $result['polkadot_id'],
					'name' => $this->language->get('text_card_use') . ' ' . $result['card_number']
				];
			}
			$method_data = [
				'code'       => 'polkadot',
				'name'       => $this->language->get('heading_title'),
				'option'     => $option_data,
				'sort_order' => $this->config->get('payment_polkadot_sort_order')
			];
		}

*/






		return $method_data;
	}


/*
	public function getPolkadot(int $customer_id, int $polkadot_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "polkadot` WHERE `customer_id` = '" . (int)$customer_id . "' AND `polkadot_id` = '" . (int)$polkadot_id . "'");
		return $query->row;
	}

	public function getPolkadots(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "polkadot` WHERE `customer_id` = '" . (int)$customer_id . "'");
		return $query->rows;
	}

	public function addPolkadot(int $customer_id, array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "polkadot` SET `customer_id` = '" . (int)$customer_id . "', `card_name` = '" . $this->db->escape($data['card_name']) . "', `card_number` = '" . $this->db->escape($data['card_number']) . "', `card_expire_month` = '" . $this->db->escape($data['card_expire_month']) . "', `card_expire_year` = '" . $this->db->escape($data['card_expire_year']) . "', `card_cvv` = '" . $this->db->escape($data['card_cvv']) . "', `date_added` = NOW()");
	}

	public function deletePolkadot(int $customer_id, int $polkadot_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "polkadot` WHERE `customer_id` = '" . (int)$customer_id . "' AND `polkadot_id` = '" . (int)$polkadot_id . "'");
	}

	public function charge(int $customer_id, int $order_id, float $amount, int $polkadot_id = 0): string {
		//$this->db->query("INSERT INTO `" . DB_PREFIX . "polkadot` SET `customer_id` = '" . (int)$customer_id . "', `card_name` = '" . $this->db->escape($data['card_name']) . "', `card_number` = '" . $this->db->escape($data['card_number']) . "', `card_expire_month` = '" . $this->db->escape($data['card_expire_month']) . "', `card_expire_year` = '" . $this->db->escape($data['card_expire_year']) . "', `card_cvv` = '" . $this->db->escape($data['card_cvv']) . "', `date_added` = NOW()");
		return $this->config->get('payment_polkadot_response');
	}
*/
}
