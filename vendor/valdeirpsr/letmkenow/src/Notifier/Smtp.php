<?php
namespace ValdeirPsr\Letmeknow\Notifier;

use Opencart\System\Engine\Config;
use Opencart\System\Library\Mail as OcMail;

class Smtp extends Mail
{
    const EXTENSION_PREFIX = 'module_letmeknow_';
    
    /**
     * see {@inheritDoc}
     */
    public function __construct(protected Config $config)
    {
        $this->mail = new OcMail('smtp');
        $this->mail->smtp_hostname = $config->get('config_mail_smtp_hostname');
        $this->mail->smtp_username = $config->get('config_mail_smtp_username');
        $this->mail->smtp_password = html_entity_decode($config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $this->mail->smtp_port = $config->get('config_mail_smtp_port');
        $this->mail->smtp_timeout = $config->get('config_mail_smtp_timeout');
    }
}