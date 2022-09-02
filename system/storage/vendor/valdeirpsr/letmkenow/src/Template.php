<?php
namespace ValdeirPsr\Letmeknow;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Opencart\System\Engine\Config;

class Template
{
    const EXTENSION_PREFIX = 'module_letmeknow_';

    /** @var Environment */
    private $twig;

    public function __construct(Config $config)
    {
        $templateConfig = [
            'charset'     => 'utf-8',
            'autoescape'  => false,
            'debug'       => false,
            'auto_reload' => true,
        ];

        $templateLoader = new ArrayLoader(
            ['template.twig' => html_entity_decode($config->get(self::EXTENSION_PREFIX . 'template_html'), ENT_NOQUOTES, 'UTF-8')],
            $templateConfig
        );

        $this->twig = new Environment($templateLoader);
    }

    /**
     * Captura o conteúdo renderizado do template
     * 
     * @param array $data
     * 
     * @return string
     */
    public function getContents(array $data): string
    {
        return $this->twig->render('template.twig', $data);
    }
}