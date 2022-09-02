<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\Events;

class DispatchNotify extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_HISTORY = 'extension/' . self::EXTENSION_CODE . '/history';

    public function index($router, $args)
    {
        [ $productId, $productInfo ] = $args;

        if ($productInfo['quantity'] <= $this->config->get(self::EXTENSION_PREFIX . 'product_quantity')) {
            return;
        }

        $this->request->post['product_id'] = $productId;
        $this->request->post['email'] = '';

        $this->load->controller(self::EXTENSION_PATH_HISTORY . '/notify');
    }
}