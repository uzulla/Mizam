<?php
declare(strict_types=1);

namespace Mizam;

use Exception;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
    /** @var Logger */
    static private $eventLogger = null;
    /** @var Logger */
    static private $errorLogger = null;
    /** @var Logger */
    static private $debugLogger = null;

    /**
     * @param string $message
     * @param array $context
     * @throws Exception
     */
    static public function event(string $message, array $context = []): void
    {
        static::getEventLogger()->notice($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @throws Exception
     */
    static public function error(string $message, array $context = []): void
    {
        static::getErrorLogger()->error($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @throws Exception
     */
    static public function debug(string $message, array $context = []): void
    {
        static::getDebugLogger()->debug($message, $context);
    }

    /**
     * @throws Exception
     */
    static public function getEventLogger(): Logger
    {
        if (is_null(static::$eventLogger)) {
            $logger = new Logger('event');
            $handler = getenv("EVENT_LOG_HANDLER_TYPE");

            switch ($handler) {
                case "disable":
                    $logger->pushHandler(new NullHandler());
                    break;

                case "stream":
                    $log_path = getenv("EVENT_LOG_PATH");
                    $logger->pushHandler(new StreamHandler($log_path, Logger::NOTICE));
                    break;

                default:
                    throw new InvalidArgumentException("invalid EVENT_LOG_HANDLER_TYPE: {$handler}");
            }
            static::$eventLogger = $logger;
        }
        return static::$eventLogger;
    }

    /**
     * @throws Exception
     */
    static public function getErrorLogger(): Logger
    {
        if (is_null(static::$errorLogger)) {
            $logger = new Logger('error');
            $handler = getenv("ERROR_LOG_HANDLER_TYPE");

            switch ($handler) {
                case "disable":
                    $logger->pushHandler(new NullHandler());
                    break;

                case "stream":
                    $log_path = getenv("ERROR_LOG_PATH");
                    $stream_handler = new StreamHandler($log_path, Logger::ERROR);
                    // エラーログは改行を有効に
                    $formatter = new LineFormatter(null, null, true);
                    $stream_handler->setFormatter($formatter);
                    $logger->pushHandler($stream_handler);
                    break;

                default:
                    throw new InvalidArgumentException("invalid ERROR_LOG_HANDLER_TYPE: {$handler}");
            }
            static::$errorLogger = $logger;
        }
        return static::$errorLogger;
    }

    /**
     * @throws Exception
     */
    static public function getDebugLogger(): Logger
    {
        if (is_null(static::$debugLogger)) {
            $logger = new Logger('debug');
            $handler = getenv("DEBUG_LOG_HANDLER_TYPE");

            switch ($handler) {
                case "disable":
                    $logger->pushHandler(new NullHandler());
                    break;

                case "stream":
                    $log_path = getenv("DEBUG_LOG_PATH");
                    $stream_handler = new StreamHandler($log_path, Logger::DEBUG);
                    // デバッグログは改行を有効に
                    $formatter = new LineFormatter(null, null, true);
                    $stream_handler->setFormatter($formatter);
                    $logger->pushHandler($stream_handler);
                    break;

                default:
                    throw new InvalidArgumentException("invalid DEBUG_LOG_HANDLER_TYPE: {$handler}");
            }
            static::$debugLogger = $logger;
        }
        return static::$debugLogger;
    }

    /**
     * @throws Exception
     */
    static public function dumpOutputBufferToLog(): void
    {
        $something = ob_get_contents();
        ob_clean();

        if (strlen($something) > 0) {
            Log::error("un excepted stdout output", ["output" => $something]);
        }
    }
}
