<?php
namespace OpenCart\Admin\Model\Extension\LetMeKnowWheItsAvailable\Module;

class Config extends \OpenCart\System\Engine\Model
{
    public function createTables(): void
    {
        $this->db->query('
            CREATE TABLE `' . DB_PREFIX . 'let_me_know` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `customer_name` VARCHAR(64) NOT NULL,
                `customer_email` VARCHAR(255) NOT NULL,
                `product_id` INT(11) NOT NULL,
                `language_id` INT(11) NOT NULL,
                `custom_fields` JSON NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `concluded_at` DATETIME NULL,
                `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
        ');
    }

    public function dropTables(): void
    {
        $this->db->query('DROP TABLE `' . DB_PREFIX . 'let_me_know`');
    }
}