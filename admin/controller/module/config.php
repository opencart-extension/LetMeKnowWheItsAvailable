<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\Module;

class Config extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_MODULE = 'extension/' . self::EXTENSION_CODE . '/module';

    public function index(): void
    {
        $data = [];

        $this->load->language(self::EXTENSION_PATH_MODULE . '/config');

        $this->document->setTitle($this->language->get('heading_title'));

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

        $data['action'] = $this->url->link(self::EXTENSION_PATH_MODULE . '/config|save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extensions', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view(self::EXTENSION_PATH_MODULE . '/config', $data));
    }

    /**
     * Cria tabelas e eventos do módulo após instalação da extensão
     * 
     * @return void
     */
    public function install(): void
    {
        $this->load->model(self::EXTENSION_PATH_MODULE . '/config');

        $this->{self::EXTENSION_MODEL}->createTables();

        $this->load->model('setting/event');

        $this->model_setting_event->addEvent([
            'code' => self::EXTENSION_CODE,
            'description' => 'Change Theme',
            'trigger' => 'catalog/view/product/product/after',
            'action' => self::EXTENSION_PATH_MODULE . '/button',
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
        $this->load->model(self::EXTENSION_PATH_MODULE . '/config');

        $this->{self::EXTENSION_MODEL}->dropTables();

        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode(self::EXTENSION_CODE);
    }
}