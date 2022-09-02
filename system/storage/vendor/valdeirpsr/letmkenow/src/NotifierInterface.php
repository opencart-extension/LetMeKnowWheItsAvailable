<?php
namespace ValdeirPsr\Letmeknow;

use Opencart\System\Engine\Config;

interface NotifierInterface
{
    /**
     * @param Config $config
     */
    public function __construct(Config $config);

    /**
     * @param array $registers
     */
    public function send(array $registers): array;
}