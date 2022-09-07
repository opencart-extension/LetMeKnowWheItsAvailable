<?php
namespace OpenCart\Catalog\Controller\Extension\LetMeKnowWheItsAvailable\Events;

class Button extends \OpenCart\System\Engine\Controller {
    const EXTENSION_PREFIX = 'module_letmeknow_';

    public function index(&$router, &$data, &$output)
    {
        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($data['product_id']);
        $stock_status_id = $this->config->get(self::EXTENSION_PREFIX . 'stock_status_id');

        if ($product_info['quantity'] === 0 && $product_info['stock_status_id'] <= $stock_status_id) {
            $buttonTitles = $this->config->get(self::EXTENSION_PREFIX . 'button_title');

            $buttonTitle = $buttonTitles[$this->config->get('config_language_id')] ?? reset($buttonTitles);

            $htmlButton = sprintf(
                '<button type="button" button-letmeknow class="btn" style="%s" data-product-id="%s">%s</button>',
                $this->config->get(self::EXTENSION_PREFIX . 'button_css_text'),
                $data['product_id'],
                $buttonTitle
            );

            $output = preg_replace(
                '/<[^>]*id=(?:\'|")button-cart(?:\'|").+<\/[^>]*>/',
                $htmlButton,
                $output
            );
        }
    }

    public function script()
    {
        $this->document->addScript('extension/LetMeKnowWheItsAvailable/catalog/view/javascript/button.js', 'footer');
    }
}