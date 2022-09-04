<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\History;

use Aws\Sqs\Exception\SqsException\{
    TooManyEntriesInBatchRequest,
    EmptyBatchRequest,
    BatchEntryIdsNotDistinct,
    BatchRequestTooLong,
    InvalidBatchEntryId,
    UnsupportedOperation
};
use ValdeirPsr\Letmeknow\NotifierInterface;
use ValdeirPsr\Letmeknow\Notifier\{
    AmazonQueueService,
    Mail,
    Smtp
};
use ValdeirPsr\Letmeknow\Logger;

class Notify extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_HISTORY = 'extension/' . self::EXTENSION_CODE . '/history';
    const EXTENSION_MODEL_NOTIFIER = 'model_extension_' . self::EXTENSION_CODE . '_history_notifier';

    /**
     * Notifica usuário
     */
    public function index(): void
    {
        if (!class_exists('\\ValdeirPsr\\Letmeknow\\Sender')) {
            require_once DIR_EXTENSION . self::EXTENSION_CODE . '/vendor/autoload.php';
        }

        $this->load->language(self::EXTENSION_PATH_HISTORY . '/notify');

        define('LETMEKNOW_LOG', DIR_EXTENSION . self::EXTENSION_CODE . '/system/storage/logs');

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        $this->load->model(self::EXTENSION_PATH_HISTORY . '/notifier');

        $emails = $this->{self::EXTENSION_MODEL_NOTIFIER}->getEmails($productId, $email);

        Logger::getInstance();

        $mail = $this->getNotifierEngine();

        $json = [];

        if ($mail === null) {
            $json['error'] = $this->language->get('error_engine_default_null');
        }

        if (!$json) {
            try {
                $sender = new \ValdeirPsr\Letmeknow\Sender($mail);
                $sender->setRegisters($emails);
                $confirmed = $sender->send($emails);

                $this->{self::EXTENSION_MODEL_NOTIFIER}->confirm($productId, $confirmed);
            } catch (TooManyEntriesInBatchRequest $e) {
                Logger::error($e->getMessage(), ['Obj' => $e]);
                $json['error'] = $this->language->get('error_too_many_entries_in_batch_request');
            } catch (EmptyBatchRequest $e) {
                Logger::warning($e->getMessage(), ['Obj' => $e]);
                $json['error'] = $this->language->get('error_empty_batch_request');
            } catch (BatchEntryIdsNotDistinct $e) {
                Logger::error($e->getMessage(), ['Obj' => $e]);
                $json['error'] = $this->language->get('error_batch_entry_ids_not_distinct');
            } catch (BatchRequestTooLong $e) {
                Logger::warning($e->getMessage(), ['Obj' => $e]);
                $json['error'] = $this->language->get('error_batch_request_too_long');
            } catch (InvalidBatchEntryId $e) {
                Logger::error($e->getMessage(), ['Obj' => $e]);
                $json['error'] = $this->language->get('error_invalid_batch_entry_id');
            } catch (UnsupportedOperation $e) {
                Logger::error($e->getMessage(), ['Obj' => $e]);
                $json['error'] = $this->language->get('error_unsupported_operation');
            } catch (\Exception $e) {
                Logger::error($e->getMessage(), ['Obj' => $e]);
                $json['error'] = $this->language->get('error_unknow');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getNotifierEngine(): NotifierInterface|null
    {
        $notification_type = $this->config->get(self::EXTENSION_PREFIX . 'notification_type');

        switch ($notification_type) {
            case 'default':
                return $this->getEngineDefault();
            case 'smtp':
                return $this->getEngineCustomSmtp();
            case 'sqs':
                return new AmazonQueueService($this->config);
        }
    }

    private function getEngineDefault(): NotifierInterface|null
    {
        if ($this->config->get('config_mail_engine') === 'smtp') {
            return new Smtp($this->config);
        } else if ($this->config->get('config_mail_engine') === 'mail') {
            return new Mail($this->config);
        } else {
            Logger::warning($this->language->get('error_none_engine_mail'));
        }

        return null;
    }

    private function getEngineCustomSmtp(): NotifierInterface
    {
        $this->config->set('config_mail_smtp_hostname', $this->config->get(self::EXTENSION_PREFIX . 'smtp_hostname'));
        $this->config->set('config_mail_smtp_username', $this->config->get(self::EXTENSION_PREFIX . 'smtp_username'));
        $this->config->set('config_mail_smtp_password', $this->config->get(self::EXTENSION_PREFIX . 'smtp_password'));
        $this->config->set('config_mail_smtp_port', $this->config->get(self::EXTENSION_PREFIX . 'smtp_port'));
        $this->config->set('config_mail_smtp_timeout', $this->config->get(self::EXTENSION_PREFIX . 'smtp_timeout'));

        return new \ValdeirPsr\Letmeknow\Notifier\Smtp($this->config);
    }
}