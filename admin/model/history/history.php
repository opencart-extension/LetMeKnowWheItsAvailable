<?php
namespace OpenCart\Admin\Model\Extension\LetMeKnowWheItsAvailable\History;

class History extends \OpenCart\System\Engine\Model
{
    public function getUser(string $id): array
    {
        $sql = "
            SELECT l.*, pd.name as product_name
            FROM `" . DB_PREFIX . "let_me_know` l
            LEFT JOIN `" . DB_PREFIX . "product_description` pd
                ON (pd.product_id = l.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                WHERE `id` = '" . $this->db->escape($id) . " '
        ";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function getUsers(array $filter): array
    {
        $sql = "
            SELECT l.*, pd.name as product_name
            FROM `" . DB_PREFIX . "let_me_know` l
            LEFT JOIN `" . DB_PREFIX . "product_description` pd
                ON (pd.product_id = l.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                WHERE 1 = 1
        ";

        if ($filter['filter_product_name']) {
            $sql .= " AND pd.name = '" . $this->db->escape($filter['filter_product_name']) . "'";
        }

        if ($filter['filter_status'] !== false) {
            if (intval($filter['filter_status']) === 1) {
                $sql .= " AND l.concluded_at IS NOT null";
            } elseif (intval($filter['filter_status']) === 0) {
                $sql .= " AND l.concluded_at IS null";
            }
        }

        if ($filter['limit']) {
            $sql .= " LIMIT " . (int)$filter['limit'];
        }

        if ($filter['start']) {
            $sql .= " OFFSET " . (int)$filter['start'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getUsersTotal(array $filter): int
    {
        $sql = "
            SELECT id
            FROM `" . DB_PREFIX . "let_me_know` l
            LEFT JOIN `" . DB_PREFIX . "product_description` pd
                ON (pd.product_id = l.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                WHERE 1 = 1
        ";

        if ($filter['filter_product_name']) {
            $sql .= " AND pd.name = '" . $this->db->escape($filter['filter_product_name']) . "'";
        }

        if ($filter['filter_status'] !== false) {
            if (intval($filter['filter_status']) === 1) {
                $sql .= " AND l.concluded_at IS NOT null";
            } elseif (intval($filter['filter_status']) === 0) {
                $sql .= " AND l.concluded_at IS null";
            }
        }

        $query = $this->db->query($sql);

        return $query->num_rows;
    }
}