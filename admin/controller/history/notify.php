<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\History;

class Notify extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_MODULE = 'extension/' . self::EXTENSION_CODE . '/module';
    const EXTENSION_MODEL_NOTIFIER = 'model_extension_' . self::EXTENSION_CODE . '_module_notifier';

    /**
     * Notifica usuário
     */
    public function index(): void
    {
        if (!class_exists('\\ValdeirPsr\\Letmeknow\\Sender')) {
            require_once DIR_EXTENSION . self::EXTENSION_CODE . '/vendor/autoload.php';
        }

        define('LETMEKNOW_LOG', DIR_EXTENSION . self::EXTENSION_CODE . '/system/storage/logs');

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        $this->load->model(self::EXTENSION_PATH_MODULE . '/notifier');

        $emails = $this->{self::EXTENSION_MODEL_NOTIFIER}->getEmails($productId, $email);

        $mail = new \ValdeirPsr\Letmeknow\Notifier\Smtp($this->config);
        $sender = new \ValdeirPsr\Letmeknow\Sender($mail);
        $sender->setRegisters($emails);
        $confirmed = $sender->send($emails);

        $this->{self::EXTENSION_MODEL_NOTIFIER}->confirm($productId, $confirmed);
    }
}