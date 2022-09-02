<?php
namespace OpenCart\Admin\Controller\Extension\LetMeKnowWheItsAvailable\Events;

class Menu extends \OpenCart\System\Engine\Controller
{
    const EXTENSION_PREFIX = 'module_letmeknow_';
    const EXTENSION_CODE = 'LetMeKnowWheItsAvailable';
    const EXTENSION_PATH_HISTORY = 'extension/' . self::EXTENSION_CODE . '/history';

    public function index($router, &$data, $code)
    {
        if ($this->config->get(self::EXTENSION_PREFIX . 'status')) {
            array_splice($data['menus'], 1, 0, [
                [
                    'id'       => 'menu-dashboard',
                    'icon'     => 'fas fa-bell',
                    'name'     => 'Let Me Know',
                    'href'     => $this->url->link(self::EXTENSION_PATH_HISTORY . '/table', 'user_token=' . $this->session->data['user_token']),
                    'children' => []
                ]
            ]);
        }
    }
}