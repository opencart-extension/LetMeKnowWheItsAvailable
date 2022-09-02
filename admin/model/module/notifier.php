<?php
namespace OpenCart\Admin\Model\Extension\LetMeKnowWheItsAvailable\Module;

use Opencart\System\Library\Mail;
use Opencart\System\Library\Template;

class Notifier extends \OpenCart\System\Engine\Model
{
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PREFIX = 'module_letmeknow_';

    /**
     * Captura os registros de e-mails com base no ID do produto
     * 
     * @param int $productId
     * 
     * @return array
     */
    public function getEmails(int $productId, ?string $email = ''): array
    {
        $sql = "
            SELECT
                l.customer_name,
                l.customer_email,
                l.product_id,
                pd.name AS product_name,
                p.price AS product_price,
                p.image AS product_image,
                pd.description AS product_description
            FROM `" . DB_PREFIX . "let_me_know` l
            LEFT JOIN `" . DB_PREFIX . "product` p ON (p.product_id = l.product_id)
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = l.product_id AND pd.language_id = l.language_id)
            WHERE l.`product_id` = '" . $productId . "' and l.`concluded_at` IS NULL
        ";

        if ($email) {
            $sql .= " AND l.`customer_email` = '" . $this->db->escape($email) . "'";
        }

        $query = $this->db->query($sql);

        $rows = [];

        foreach ($query->rows as $row) {
            $row['product_price'] = $this->currency->format($row['product_price'], $this->config->get('config_currency'));
            $row['product_image'] = HTTP_CATALOG . 'image/' . $row['product_image'];
            $row['product_description'] = html_entity_decode($row['product_description'], ENT_HTML5, 'UTF-8');
            $row['product_link'] = HTTP_CATALOG . 'index.php?route=product/product&product_id=' . $row['product_id'];

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Confirma o envio
     * 
     * @param array $emails
     * 
     * @return void
     */
    public function confirm(int $productId, array $emails): void
    {
        $emails = array_map(fn($item) => $this->db->escape($item), $emails);

        $this->db->query("
            UPDATE `" . DB_PREFIX . "let_me_know`
            SET `concluded_at` = CURRENT_TIMESTAMP,
                `modified_at` = CURRENT_TIMESTAMP
            WHERE `customer_email` IN ('" . implode("','", $emails) . "')
                AND `product_id` = '" . $productId . "'
        ");
    }
}