<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\History;

class Info extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_HISTORY = 'extension/' . self::EXTENSION_CODE . '/history';
    const EXTENSION_MODEL_HISTORY = 'model_extension_' . self::EXTENSION_CODE . '_history_history';

    /**
     * Exibe uma tabela com os dados dos campos personalizados
     * 
     * @return void
     */
    public function index(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $data = [];

        if ($id) {
            $this->load->model('customer/custom_field');

            $this->load->model(self::EXTENSION_PATH_HISTORY . '/history');

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

        $this->response->setOutput($this->load->view(self::EXTENSION_PATH_HISTORY . '/table_info', $data));
    }
}