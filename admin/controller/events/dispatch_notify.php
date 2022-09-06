<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\Events;

class DispatchNotify extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_NOTIFY = 'extension/' . self::EXTENSION_CODE . '/history/notify';
    const EXTENSION_PATH_NOTIFIER = 'extension/' . self::EXTENSION_CODE . '/history/notifier';
    const EXTENSION_MODEL_NOTIFIER = 'model_extension_' . self::EXTENSION_CODE . '_history_notifier';

    public function index($router, $args)
    {
        [ $productId, $productInfo ] = $args;

        if ($productInfo['quantity'] <= $this->config->get(self::EXTENSION_PREFIX . 'product_quantity')) {
            return;
        }

        $this->load->model(self::EXTENSION_PATH_NOTIFIER);

        $ids = $this->{self::EXTENSION_MODEL_NOTIFIER}->getRegistersIdByProductId($productId);
        $ids = array_map(fn($item) => $item['id'], $ids);

        $this->request->post['ids'] = implode(",", $ids);

        $this->load->controller(self::EXTENSION_PATH_NOTIFY);
    }
}