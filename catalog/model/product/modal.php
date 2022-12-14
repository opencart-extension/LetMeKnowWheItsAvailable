<?php
namespace OpenCart\Catalog\Model\Extension\LetMeKnowWheItsAvailable\Product;

class Modal extends \OpenCart\System\Engine\Model {
    public function add(
        string $customer_name,
        string $customer_email,
        int $product_id,
        array $custom_fields = [],
    ) {
        $sql = "
            INSERT INTO `" . DB_PREFIX . "let_me_know`
            SET `customer_name` = '" . $this->db->escape($customer_name) . "',
                `customer_email` = '" . $this->db->escape($customer_email) . "',
                `product_id` = '" . $product_id . "',
                `product_price_old` = (SELECT p.`price` FROM `" . DB_PREFIX . "product` p WHERE p.product_id = '" . $product_id . "'),
                `language_id` = '" . (int)$this->config->get('config_language_id') . "',
                `custom_fields` = '" . $this->db->escape(json_encode($custom_fields)) . "',
                `currency_code` = '" . $this->db->escape($this->session->data['currency']) . "'
            ";

        $this->db->query($sql);
    }
}