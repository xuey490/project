<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Log;

use Framework\Config\ConfigService;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Psr\Log\LoggerInterface;  // 导入 LoggerInterface
use Stringable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;  // 引入 Stringable 接口

class LoggerService implements LoggerInterface  // 实现 Psr\Log\LoggerInterface
{
    private MonoLogger $logger;

    public function __construct(
        private ConfigService $config
    ) {
        $channel  = $this->config->get('log.log_channel', 'app');
        $logDir   = $this->config->get('log.log_path', BASE_PATH . '/storage/logs');
        $maxSize  = (int) $this->config->get('log.logSize', 5 * 1024 * 1024); // 默认 5MB
        $keepDays = (int) $this->config->get('log.logKeepDays', 30);         // 默认保留30天

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logger = new MonoLogger($channel);

        // 1. Debug 日志：仅 DEBUG
        $debugHandler = new FilterHandler(
            new FileSizeRotateHandler($logDir . '/debug.log', $maxSize, $keepDays, MonoLogger::DEBUG),
            // new StreamHandler($logDir . '/debug.log', MonoLogger::DEBUG),
            MonoLogger::DEBUG,
            MonoLogger::DEBUG
        );
        $this->logger->pushHandler($debugHandler);

        // 2. Error 日志：ERROR ~ EMERGENCY
        $errorHandler = new FilterHandler(
            new FileSizeRotateHandler($logDir . '/error.log', $maxSize, $keepDays, MonoLogger::ERROR),
            # new StreamHandler($logDir . '/error.log', MonoLogger::ERROR),
            MonoLogger::ERROR,
            MonoLogger::EMERGENCY
        );
        $this->logger->pushHandler($errorHandler);

        // 3. App 日志：INFO, NOTICE, WARNING
        $appHandler = new FilterHandler(
            // new StreamHandler($logDir . '/app.log', MonoLogger::INFO),
            new FileSizeRotateHandler($logDir . '/app.log', $maxSize, $keepDays, MonoLogger::INFO),
            MonoLogger::INFO,
            MonoLogger::WARNING
        );
        $this->logger->pushHandler($appHandler);
    }

    // 实现 Psr\Log\LoggerInterface 的方法，修改为 Stringable|string
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function getMonoLogger(): MonoLogger
    {
        return $this->logger;
    }

    public function logRequest(Request $request, ?Response $response = null, float $duration = 0): void
    {
        $this->info('Request', [
            'method'          => $request->getMethod(),
            'uri'             => $request->getRequestUri(),
            'ip'              => $request->getClientIp() ?: 'unknown',
            'user_agent'      => $request->headers->get('User-Agent') ?? 'unknown',
            'response_status' => $response?->getStatusCode(),
            'duration_ms'     => round($duration * 1000, 2),
        ]);
    }

    public function logException(\Throwable $exception, Request $request): void
    {
        $this->error('Exception', [
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
            'method'  => $request->getMethod(),
            'uri'     => $request->getRequestUri(),
            'ip'      => $request->getClientIp() ?: 'unknown',
        ]);
    }
}
