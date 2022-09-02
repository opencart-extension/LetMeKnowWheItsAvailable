<?php
namespace ValdeirPsr\Letmeknow\Notifier;

use ValdeirPsr\Letmeknow\NotifierInterface;
use ValdeirPsr\Letmeknow\Logger;
use Aws\Sqs\SqsClient;
use Opencart\System\Engine\Config;

class AmazonQueueService implements NotifierInterface
{
    const EXTENSION_PREFIX = 'module_letmeknow_';

    /** @var SqsClient */
    private $sqs;

    /** @var int */
    private $batchTotal = 1;

    /** @var string */
    private $queueUrl;

    /**
     * see {@inheritDoc}
     */
    public function __construct(Config $config)
    {
        $this->sqs = new SqsClient([
            'version' => '2012-11-05',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => $config->get(self::EXTENSION_PREFIX . 'sqs_access_key'),
                'secret' => $config->get(self::EXTENSION_PREFIX . 'sqs_secret_key')
            ]
        ]);

        $this->batchTotal = (int)$config->get(self::EXTENSION_PREFIX . 'sqs_queue_batch');
        $this->queueUrl = $config->get(self::EXTENSION_PREFIX . 'sqs_queue_url');

        if (empty($this->queueUrl)) {
            throw new \Exception('QueueUrl not found');
        }

        Logger::getInstance();
    }

    /**
     * see {@inheritDoc}
     */
    public function send(array $registers): array
    {
        $entries = [];
        $confirmed = [];
        $unconfirmed = [];

        foreach ($registers as $key => $register) {
            $entries[] = [
                'Id' => $key,
                'MessageBody' => json_encode($register)
            ];

            $unconfirmed[] = $register['customer_email'];

            if (count($entries) % 1 === 0) {
                $this->sqs->sendMessageBatch([
                    'Entries' => $entries,
                    'QueueUrl' => $this->queueUrl
                ]);

                $confirmed = array_merge($confirmed, $unconfirmed);
                $entries = [];
                $unconfirmed = [];
            }
        }

        return $confirmed;
    }
}