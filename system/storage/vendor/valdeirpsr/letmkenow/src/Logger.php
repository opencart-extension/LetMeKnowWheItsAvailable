<?php
namespace ValdeirPsr\Letmeknow;

use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use ValdeirPsr\Letmeknow\Logger\PsrFormatter;

class Logger
{
    private static $instance;

    private static $opts = [
        'enabled' => true,
        'path' => null
    ];

    private function __construct()
    {
        /** Previning */
    }

    public static function getInstance(array $opts = []): Monolog
    {
        if (self::$instance === null) {
            self::$opts = array_merge([
                'enabled' => true
            ], $opts);

            self::init($opts);

            if (!is_dir(LETMEKNOW_LOG)) {
                mkdir(LETMEKNOW_LOG, 0777, true);
            }

            $dateFormat = "Y-m-d\TH:i:s";
            $output = "%datetime%  ::  %level_name%  ::  %message% %context% %extra%\n";
            $formatter = new PsrFormatter($dateFormat);

            $stream = new StreamHandler(LETMEKNOW_LOG . '/error.log');
            $stream->setFormatter($formatter);

            self::$instance = new Monolog('security');
            self::$instance->pushHandler($stream);
        }

        return self::$instance;
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * @param mixed  $level   The log level
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function log($level, $message, array $context = []): void
    {
        if (self::$opts['enabled']) {
            (self::getInstance())->log($level, $message, $context);
        }
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function debug($message, array $context = []): void
    {
        self::log(Monolog::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function info($message, array $context = []): void
    {
        self::log(Monolog::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function notice($message, array $context = []): void
    {
        self::log(Monolog::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function warning($message, array $context = []): void
    {
        self::log(Monolog::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function error($message, array $context = []): void
    {
        self::log(Monolog::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function critical($message, array $context = []): void
    {
        self::log(Monolog::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function alert($message, array $context = []): void
    {
        self::log(Monolog::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public static function emergency($message, array $context = []): void
    {
        self::log(Monolog::EMERGENCY, $message, $context);
    }

    /**
     * Cria as constantes necess??rias
     *
     * @param array $opts
     *
     * @return void
     */
    private static function init(array $opts = [])
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        if (!defined('LETMEKNOW_LOG')) {
            if (isset($opts['path']) && $opts['path'] !== null) {
                define('LETMEKNOW_LOG', $opts['path']);
            }

            define('LETMEKNOW_LOG', __DIR__ . DS . '..' . DS . '..' . DS . '..' . DS . 'log');
        }
    }
}