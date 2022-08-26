<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\Module;

class History extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_MODULE = 'extension/' . self::EXTENSION_CODE . '/module';
    const EXTENSION_MODEL = 'model_extension_' . self::EXTENSION_CODE . '_module';
    const EXTENSION_MODEL_HISTORY = 'model_extension_' . self::EXTENSION_CODE . '_module_history';

    /**
     * Exibe a lista de inscrições
     * 
     * @return void
     */
    public function index(): void
    {
        $data = [];

        $this->load->language(self::EXTENSION_PATH_MODULE . '/history');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_dashboard'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => ''
        ];

        $data['list'] = $this->getList();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view(self::EXTENSION_PATH_MODULE . '/history', $data));
    }

    /**
     * Exibe lista de registros dos usuários
     */
    public function list(): void
    {
        $this->load->language(self::EXTENSION_PATH_MODULE . '/history');

        $this->response->setOutput($this->getList());
    }

    /**
     * ´Retorna lista de registros dos usuários
     * 
     * @return string
     */
    private function getList(): string
    {
        $data = [];

        $filter_product_name = $this->request->get['filter_product_name'] ?? false;
        $filter_status = $this->request->get['filter_status'] ?? false;

        $page = intval($this->request->get['page'] ?? 1);

        $filter_data = [
            'filter_product_name' => $filter_product_name,
            'filter_status' => $filter_status,
            'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
            'limit' => $this->config->get('config_pagination_admin')
        ];

        $this->load->model(self::EXTENSION_PATH_MODULE . '/history');

        $data['users'] = $this->{self::EXTENSION_MODEL_HISTORY}->getUsers($filter_data);

        $data['users'] = array_map(function($user) {
            return [
                ...$user,
                'product_edit' => $this->url->link('catalog/product|form', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $user['product_id'])
            ];
        }, $data['users']);
        
        $users_total = $this->{self::EXTENSION_MODEL_HISTORY}->getUsersTotal($filter_data);

        $url = '';

        if ($filter_product_name) {
            $url .= '&filter_product_name=' . urlencode(html_entity_decode($filter_product_name, ENT_QUOTES, 'UTF-8'));
        }

        if ($filter_status) {
            $url .= '&filter_status=' . urlencode(html_entity_decode($filter_status, ENT_QUOTES, 'UTF-8'));
        }

        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $users_total,
            'page'  => $page,
            'limit' => $this->config->get('config_pagination_admin'),
            'url'   => $this->url->link(self::EXTENSION_PATH_MODULE . '/history|list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
        ]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($users_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($users_total - $this->config->get('config_pagination_admin'))) ? $users_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $users_total, ceil($users_total / $this->config->get('config_pagination_admin')));

        return $this->load->view(self::EXTENSION_PATH_MODULE . '/history_list', $data);
    }

    public function info(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $data = [];

        if ($id) {
            $this->load->model('customer/custom_field');

            $this->load->model(self::EXTENSION_PATH_MODULE . '/history');

            $user = $this->{self::EXTENSION_MODEL_HISTORY}->getUser($id);

            if ($user) {
                $custom_fields = json_decode($user['custom_fields'], true);

                foreach ($custom_fields as $custom_field_id => $value) {
                    $custom_field_info = $this->model_customer_custom_field->getCustomField($custom_field_id);

                    if ($custom_field_info['type'] === 'checkbox') {
                        $value = implode(', ', $value);
                    } elseif ($custom_field_info['type'] == 'file' && $value) {
                        $value = '<a href="' . $this->url->link('tool/upload|download', 'user_token=' . $this->session->data['user_token'] . '&code=' . $value) . '">' . $value . '</a>';
                    }

                    $data['fields'][] = [
                        'key' => $custom_field_info['name'],
                        'value' => $value
                    ];
                }
            }
        }

        $this->response->setOutput($this->load->view(self::EXTENSION_PATH_MODULE . '/history_info', $data));
    }
}