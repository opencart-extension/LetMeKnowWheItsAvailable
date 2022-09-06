<?php
namespace OpenCart\Admin\Model\Extension\LetMeKnowWheItsAvailable\History;

use Opencart\System\Library\Mail;
use Opencart\System\Library\Template;

class Notifier extends \OpenCart\System\Engine\Model
{
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PREFIX = 'module_letmeknow_';

    /**
     * Captura os registros de e-mails com base no ID do produto
     * 
     * @param array<int> $ids
     * 
     * @return array
     */
    public function getRegistersById(array $ids): array
    {
        $sql = "
            SELECT
                l.id,
                l.customer_name,
                l.customer_email,
                l.product_id,
                l.currency_code,
                pd.name AS product_name,
                p.price AS product_price,
                p.image AS product_image,
                pd.description AS product_description,
                seo.keyword AS product_slug
            FROM `" . DB_PREFIX . "let_me_know` l
            LEFT JOIN `" . DB_PREFIX . "product` p ON (p.product_id = l.product_id)
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = l.product_id AND pd.language_id = l.language_id)
            LEFT JOIN `" . DB_PREFIX . "seo_url` seo ON (seo.value = l.product_id AND seo.key = 'product_id' AND seo.language_id = l.language_id)
            WHERE l.`id` IN ('" . implode("','", $ids) . "') and l.`concluded_at` IS NULL
        ";
        
        $query = $this->db->query($sql);

        $rows = [];

        foreach ($query->rows as $row) {
            $row['product_price'] = $this->currency->format($row['product_price'], $row['currency_code'] ?? $this->config->get('config_currency'));
            $row['product_image'] = HTTP_CATALOG . 'image/' . $row['product_image'];
            $row['product_description'] = html_entity_decode($row['product_description'], ENT_HTML5, 'UTF-8');

            if ($row['product_slug'] && $this->config->get('config_seo_url')) {
                $row['product_link'] = HTTP_CATALOG . $row['product_slug'];
            } else {
                $row['product_link'] = HTTP_CATALOG . 'index.php?route=product/product&product_id=' . $row['product_id'];
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function getRegistersIdByProductId(int $productId): array
    {
        $sql = "
            SELECT `id` FROM `" . DB_PREFIX . "let_me_know`
            WHERE `product_id` = '" . $productId . "'
                AND `concluded_at` IS NULL
        ";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Confirma o envio
     * 
     * @param array<int,int> $ids
     * 
     * @return void
     */
    public function confirm(array $ids): void
    {
        $ids = array_map(fn($item) => intval($item), $ids);

        $this->db->query("
            UPDATE `" . DB_PREFIX . "let_me_know`
            SET `concluded_at` = CURRENT_TIMESTAMP,
                `modified_at` = CURRENT_TIMESTAMP
            WHERE `id` IN ('" . implode("','", $ids) . "')
        ");
    }
}