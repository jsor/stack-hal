<?php

namespace Jsor\Stack\Hal;

use Jsor\Stack\Hal\Response\VndErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Converts to a vnd.error response.
 * @see https://github.com/blongden/vnd.error
 */
class ExceptionConverter implements HttpKernelInterface
{
    private $app;
    private $logger;
    private $prettyPrint;
    private $debug;
    private $passThroughCatch;
    private $formats;

    public function __construct(
        HttpKernelInterface $app,
        LoggerInterface $logger = null,
        $prettyPrint = true,
        $debug = false,
        $passThroughCatch = false,
        array $formats = null
    ) {
        $this->app = $app;
        $this->logger = $logger;
        $this->prettyPrint = (bool) $prettyPrint;
        $this->debug = (bool) $debug;
        $this->passThroughCatch = (bool) $passThroughCatch;
        $this->formats = $formats;
    }

    public function handle(
        Request $request,
        $type = HttpKernelInterface::MASTER_REQUEST,
        $catch = true
    ) {
        try {
            return $this->app->handle(
                $request,
                $type,
                $this->passThroughCatch ? $catch : false
            );
        } catch (\Exception $exception) {
            if (!$catch) {
                throw $exception;
            }

            $response = self::handleThrowable(
                $exception,
                $request,
                $this->logger,
                $this->prettyPrint,
                $this->debug,
                $this->formats
            );

            if ($response instanceof Response) {
                return $response;
            }

            throw $exception;
        }
    }

    public static function handleThrowable(
        \Throwable $throwable,
        Request $request,
        LoggerInterface $logger = null,
        $prettyPrint = true,
        $debug = false,
        array $formats = null
    ) {
        if (null !== $logger) {
            self::logThrowable($logger, $throwable);
        }

        $formats = $formats ?: ['json', 'xml'];

        $format = $request->getRequestFormat(null);

        if (!$format || !\in_array($format, $formats)) {
            return;
        }

        return VndErrorResponse::fromThrowable(
            $throwable,
            $prettyPrint,
            $debug
        );
    }

    public static function logThrowable(
        LoggerInterface $logger,
        \Throwable $throwable
    ) {
        $message = \sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            \get_class($throwable),
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine()
        );

        $isCritical = !$throwable instanceof HttpExceptionInterface ||
                      $throwable->getStatusCode() >= 500;
        $context = ['exception' => $throwable];

        if ($isCritical) {
            $logger->critical($message, $context);
        } else {
            $logger->error($message, $context);
        }
    }
}
