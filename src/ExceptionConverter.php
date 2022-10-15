<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal;

use Exception;
use Jsor\Stack\Hal\Response\VndErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

use function get_class;
use function in_array;

/**
 * Converts to a vnd.error response.
 *
 * @see https://github.com/blongden/vnd.error
 */
final class ExceptionConverter implements HttpKernelInterface
{
    private HttpKernelInterface $app;
    private ?LoggerInterface $logger;
    private bool $prettyPrint;
    private bool $debug;
    private bool $passThroughCatch;
    private ?array $formats;

    public function __construct(
        HttpKernelInterface $app,
        LoggerInterface $logger = null,
        bool $prettyPrint = true,
        bool $debug = false,
        bool $passThroughCatch = false,
        array $formats = null,
    ) {
        $this->app = $app;
        $this->logger = $logger;
        $this->prettyPrint = $prettyPrint;
        $this->debug = $debug;
        $this->passThroughCatch = $passThroughCatch;
        $this->formats = $formats;
    }

    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true,
    ): Response {
        try {
            return $this->app->handle(
                $request,
                $type,
                $this->passThroughCatch ? $catch : false,
            );
        } catch (Exception $exception) {
            if (!$catch) {
                throw $exception;
            }

            $response = self::handleThrowable(
                $exception,
                $request,
                $this->logger,
                $this->prettyPrint,
                $this->debug,
                $this->formats,
            );

            if ($response instanceof Response) {
                return $response;
            }

            throw $exception;
        }
    }

    public static function handleThrowable(
        Throwable $throwable,
        Request $request,
        LoggerInterface $logger = null,
        bool $prettyPrint = true,
        bool $debug = false,
        array $formats = null,
    ): ?VndErrorResponse {
        if (null !== $logger) {
            self::logThrowable($logger, $throwable);
        }

        $formats = $formats ?: ['json', 'xml'];

        $format = $request->getRequestFormat(null);

        if (!$format || !in_array($format, $formats, true)) {
            return null;
        }

        return VndErrorResponse::fromThrowable(
            $throwable,
            $prettyPrint,
            $debug,
        );
    }

    public static function logThrowable(
        LoggerInterface $logger,
        Throwable $throwable,
    ): void {
        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($throwable),
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine(),
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
