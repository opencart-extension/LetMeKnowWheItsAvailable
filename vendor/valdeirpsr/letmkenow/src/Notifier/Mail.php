<?php
namespace ValdeirPsr\Letmeknow\Notifier;

use ValdeirPsr\Letmeknow\NotifierInterface;
use ValdeirPsr\Letmeknow\Logger;
use ValdeirPsr\Letmeknow\Template;
use Opencart\System\Engine\Config;
use Opencart\System\Library\Mail as OcMail;

class Mail implements NotifierInterface
{
    const EXTENSION_PREFIX = 'module_letmeknow_';

    /** @var Mail */
    protected $mail;

    /**
     * see {@inheritDoc}
     */
    public function __construct(protected Config $config)
    {
        $this->mail = new OcMail('mail');
        $this->mail->parameter = $this->config->get('config_mail_parameter');
    }

    /**
     * see {@inheritDoc}
     */
    public function send(array $registers): array
    {
        Logger::getInstance();

        $confirmed = [];

        $template = new Template($this->config);

        foreach ($registers as $register) {
            $this->mail->setTo($register['customer_email']);
            $this->mail->setSender($this->config->get('config_name'));
            $this->mail->setFrom($this->config->get('config_email'));
            $this->mail->setSubject('Product Available'); /* @todo Criar campo no painel admin */
            $this->mail->setHtml($template->getContents($register));
            
            if ($this->mail->send()) {
                $confirmed[] = $register['customer_email'];
            } else {
                Logger::error('E-mail não enviado para ' . $register['customer_email']);
            }
        }

        return $confirmed;
    }
}