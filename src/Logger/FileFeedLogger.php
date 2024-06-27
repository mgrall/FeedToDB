<?php

namespace Mgrall\FeedToDb\Logger;

use Mgrall\FeedToDb\config\Config;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * A simple logger compliant with the PSR-3 Logger Interface.
 */
class FileFeedLogger implements LoggerInterface
{
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $message = "[" . date('Y-m-d H:i:s') . "] " . strtoupper($level) . ": " . $message . " " . json_encode($context) . PHP_EOL;
        file_put_contents(Config::get('logger')['path'], $message, FILE_APPEND);
    }
}