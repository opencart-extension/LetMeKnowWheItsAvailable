<?php
namespace OpenCart\Catalog\Controller\Extension\LetMeKnowWheItsAvailable\Module;

class Modal extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_PATH_MODULE = 'extension/' . self::EXTENSION_CODE . '/module';
    const EXTENSION_MODAL = 'model_extension_' . self::EXTENSION_CODE . '_module_modal';

    /**
     * Exibe o modal para o usuário
     */
    public function index(): void {
        $this->load->language(self::EXTENSION_PATH_MODULE . '/modal');

        $data = [];

        $custom_fields_id = $this->config->get(self::EXTENSION_PREFIX . 'modal_custom_fields');

        $data['custom_fields'] = [];

		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields();

		foreach ($custom_fields as $custom_field) {
			if (in_array($custom_field['custom_field_id'], $custom_fields_id)) {
				$data['custom_fields'][] = $custom_field;
			}
		}

        $data['modal_size'] = $this->config->get(self::EXTENSION_PREFIX . 'modal_size');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view(self::EXTENSION_PATH_MODULE . '/modal', $data));
    }

    /**
     * Salva o registro ou imprime os erros
     */
    public function save(): void {
        $this->load->language(self::EXTENSION_PATH_MODULE . '/modal');

        $this->load->model('account/custom_field');

        $custom_fields_id = $this->config->get(self::EXTENSION_PREFIX . 'modal_custom_fields');

        $custom_fields = $this->model_account_custom_field->getCustomFields();
        $custom_fields = array_filter($custom_fields, fn($custom_field) => in_array($custom_field['custom_field_id'], $custom_fields_id));

        $json = [];

        if (mb_strlen($this->request->post['name']) === 0) {
            $json['error']['name'] = $this->model_account_custom_field->get('error_required');
        }

        if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $json['error']['email'] = $this->language->get('error_validation');
        }

        foreach ($custom_fields as $key => $value) {
            $input = $this->request->post['custom_field'][$value['custom_field_id']] ?? false;

            if ($value['validation'] && !preg_match($value['validation'], $input)) {
                $json['error'][$value['custom_field_id']] = $this->language->get('error_validation');
            }

            if ($value['required'] && mb_strlen($input) === 0) {
                $json['error'][$value['custom_field_id']] = $this->language->get('error_required');
            }
        }

        if (!$json) {
            $this->load->model(self::EXTENSION_PATH_MODULE . '/modal');

            $this->{self::EXTENSION_MODAL}->add(
                $this->request->post['name'],
                $this->request->post['email'],
                43,
                $this->request->post['custom_field']
            );
        }

        header('Content-Type: application/json');
        echo(json_encode($json));
    }
}