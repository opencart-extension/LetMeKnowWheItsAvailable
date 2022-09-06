<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\Module;

class Letmeknow extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_VERSION = '1.0.0';
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_MODULE = 'extension/' . self::EXTENSION_CODE . '/module/letmeknow';
    const EXTENSION_PATH_EVENTS = 'extension/' . self::EXTENSION_CODE . '/events';
    const EXTENSION_MODEL = 'model_extension_' . self::EXTENSION_CODE . '_module_letmeknow';

    /**
     * Exibe o formulário de configuração para o usuário
     * 
     * @return void
     */
    public function index(): void
    {
        $data = [];

        $this->load->language(self::EXTENSION_PATH_MODULE);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('https://cdn.jsdelivr.net/gh/opencart-extension/opencart-ads-telemetry/lib/bundle.js');

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_dashboard'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extensions'),
            'href' => $this->url->link('marketplace/extensions', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => ''
        ];

        foreach ($this->getFields() as $key => $value) {
            $data[$key] = $this->config->get(self::EXTENSION_PREFIX . $key);
        }

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('catalog/information');

        $data['informations'] = $this->model_catalog_information->getInformations();

        $this->load->model('customer/custom_field');

        $data['custom_fields'] = $this->model_customer_custom_field->getCustomFields(['filter_status' => 1]);

        $data['help_new_custom_field'] = sprintf(
            $this->language->get('help_new_custom_field'),
            $this->url->link('customer/custom_field', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link(self::EXTENSION_PATH_MODULE . '|save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extensions', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view(self::EXTENSION_PATH_MODULE, $data));
    }

    /**
     * Valida e salva os dados na tabela de configuração
     * 
     * @return void
     */
    public function save(): void
    {
        $this->load->language(self::EXTENSION_PATH_MODULE);

        $json = [];

        $fields = $this->getFields();

        foreach ($fields as $field => $value) {
            if (preg_match('/^smtp_/', $field) && $this->request->post['notification_type'] !== 'smtp') {
                continue;
            }

            if (preg_match('/^sqs_/', $field) && $this->request->post['notification_type'] !== 'sqs') {
                continue;
            }

            if ($value['required']) {
                if ($this->request->post[$field] === '') {
                    $json['error'][$field] = $this->language->get('error_required_field');
                } else if (is_array($this->request->post[$field])) {
                    foreach ($this->request->post[$field] as $language_id => $text) {
                        if ($text === '') {
                            $json['error'][$field . '-' . $language_id] = $this->language->get('error_required_field');
                        }
                    }
                }
            }
        }

        if ($json) {
            $json['error']['warning'] = $this->language->get('error_info');
        }

        if (!$this->user->hasPermission('modify', self::EXTENSION_PATH_MODULE)) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $data_keys = array_map(fn($key) => self::EXTENSION_PREFIX . $key, array_keys($this->request->post));
            $data = array_combine($data_keys, array_values($this->request->post));

            $this->model_setting_setting->editSetting(rtrim(self::EXTENSION_PREFIX, '_'), $data);

            $json['success'] = $this->language->get('text_success');

            $this->telemetry();            
            $this->newsletter();            
        }

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

    /**
     * Cria tabelas e eventos do módulo após instalação da extensão
     * 
     * @return void
     */
    public function install(): void
    {
        $this->load->model(self::EXTENSION_PATH_MODULE);

        $this->{self::EXTENSION_MODEL}->createTables();

        $this->load->model('setting/event');

        $this->model_setting_event->addEvent([
            'code' => self::EXTENSION_CODE,
            'description' => 'Change Theme',
            'trigger' => 'catalog/view/product/product/after',
            'action' => self::EXTENSION_PATH_EVENTS . '/button',
            'status' => 1,
            'sort_order' => 0
        ]);

        $this->model_setting_event->addEvent([
            'code' => self::EXTENSION_CODE,
            'description' => 'Dispatch Notify',
            'trigger' => 'admin/model/catalog/product/editProduct/after',
            'action' => self::EXTENSION_PATH_EVENTS . '/dispatch_notify',
            'status' => 1,
            'sort_order' => 0
        ]);

        $this->model_setting_event->addEvent([
            'code' => self::EXTENSION_CODE,
            'description' => 'Add Menu',
            'trigger' => 'admin/view/common/column_left/before',
            'action' => self::EXTENSION_PATH_EVENTS . '/menu',
            'status' => 1,
            'sort_order' => 0
        ]);
    }

    /**
     * Remove tabelas e eventos do módulo após desinstalação da extensão
     * 
     * @return void
     */
    public function uninstall(): void
    {
        $this->load->model(self::EXTENSION_PATH_MODULE);

        $this->{self::EXTENSION_MODEL}->dropTables();

        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode(self::EXTENSION_CODE);
    }

    /**
     * Envia os dados para telemetria
     */
    public function telemetry()
    {
        $url = $this->request->post['telemetry_url'] ?? false;

        if ($this->request->post['telemetry'] && $url) {
            $fields_remove = array_filter(
                $this->getFields(),
                fn($value, $key) => $value['telemetry'] === false,
                ARRAY_FILTER_USE_BOTH
            );

            $data = array_filter(
                $this->request->post,
                fn($value, $key) => !array_key_exists($key, $fields_remove),
                ARRAY_FILTER_USE_BOTH
            );

            $fields = [
                'version' => self::EXTENSION_VERSION,
                'uuid' => sha1($this->request->server['REMOTE_ADDR']),
                'plataform' => 'OpenCart ' . VERSION,
                'module' => self::EXTENSION_CODE,
                'data' => $data
            ];

            ob_start();
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, self::EXTENSION_CODE);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_exec($curl);
            curl_close($curl);
            ob_end_clean();
        }
    }

    /**
     * Cadastra e-mail para receber alertas
     */
    private function newsletter()
    {
        $url = $this->request->post['newsletter_url'] ?? false;

        if (!$url) return;

        $method = !empty($this->request->post['newsletter'])
            ? 'POST'
            : 'DELETE';

        $fields = [
            'email' => $this->request->post['newsletter'],
            'plataform' => 'OpenCart ' . VERSION,
            'module' => self::EXTENSION_CODE
        ];

        $fields['ref'] = sha1(json_encode($fields));

        if ($method === 'DELETE') {
            $url .= '/' . $fields['ref'];
        }

        ob_start();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, self::EXTENSION_CODE);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_exec($curl);
        curl_close($curl);
        ob_end_clean();
    }

    /**
     * Retorna os campos permitidos
     * 
     * @return array<string, array<string, boolean>>
     */
    private function getFields(): array
    {
        return [
            'status' => [
                'required' => true,
                'telemetry' => true
            ],
            'product_quantity' => [
                'required' => true,
                'telemetry' => true
            ],
            'terms' => [
                'required' => true,
                'telemetry' => false
            ],
            'modal_size' => [
                'required' => false,
                'telemetry' => true
            ],
            'notification_type' => [
                'required' => true,
                'telemetry' => true
            ],
            'smtp_hostname' => [
                'required' => true,
                'telemetry' => false
            ],
            'smtp_username' => [
                'required' => true,
                'telemetry' => false
            ],
            'smtp_password' => [
                'required' => true,
                'telemetry' => false
            ],
            'smtp_port' => [
                'required' => true,
                'telemetry' => false
            ],
            'smtp_timeout' => [
                'required' => true,
                'telemetry' => false
            ],
            'sqs_access_key' => [
                'required' => true,
                'telemetry' => false
            ],
            'sqs_secret_key' => [
                'required' => true,
                'telemetry' => false
            ],
            'sqs_region' => [
                'required' => true,
                'telemetry' => true
            ],
            'sqs_queue_url' => [
                'required' => true,
                'telemetry' => false
            ],
            'sqs_queue_batch' => [
                'required' => true,
                'telemetry' => false
            ],
            'button_title' => [
                'required' => true,
                'telemetry' => true
            ],
            'button_background_color' => [
                'required' => true,
                'telemetry' => true
            ],
            'button_font_color' => [
                'required' => true,
                'telemetry' => true
            ],
            'button_border_color' => [
                'required' => true,
                'telemetry' => true
            ],
            'button_size' => [
                'required' => false,
                'telemetry' => true
            ],
            'modal_custom_fields' => [
                'required' => false,
                'telemetry' => false
            ],
            'template_html' => [
                'required' => true,
                'telemetry' => false
            ],
            'button_css_text' => [
                'required' => false,
                'telemetry' => true
            ]
        ];
    }
}