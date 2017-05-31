<?php

namespace Jsor\Stack\Hal\Response;

use Jsor\Stack\Hal\Exception\HalException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class VndErrorResponse extends HalResponse
{
    public function __construct(
        Hal $hal,
        $status = 500,
        $headers = [],
        $prettyPrint = true
    ) {
        parent::__construct($hal, $status, $headers, $prettyPrint);

        $this->headers->set('Content-Type', 'application/vnd.error+json');
    }

    public static function create(
        $hal = null,
        $status = 500,
        $headers = [],
        $prettyPrint = true
    ) {
        return new static($hal, $status, $headers, $prettyPrint);
    }

    public static function fromException(
        \Exception $exception,
        $prettyPrint = true,
        $debug = false
    ) {
        $statusCode = self::extractStatus($exception);
        $headers    = self::extractHeaders($exception);
        $message    = self::extractMessage($exception, $debug);

        if ($exception instanceof HalException) {
            $hal = $exception->getHal();
        } else {
            $hal = new Hal(null, ['message' => $message]);
        }

        $data = $hal->getData();

        if (!isset($data['message']) || '' === $data['message']) {
            if ($message) {
                $data['message'] = $message;
            } elseif (isset(Response::$statusTexts[$statusCode])) {
                $data['message'] = Response::$statusTexts[$statusCode];
            }

            $hal->setData($data);
        }

        return new static($hal, $statusCode, $headers, $prettyPrint);
    }

    public function prepare(Request $request)
    {
        parent::prepare($request);

        if ('xml' === $request->getRequestFormat()) {
            $this->headers->set('Content-Type', 'application/vnd.error+xml');
        }

        return $this;
    }

    private static function extractStatus(\Exception $exception)
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof \Symfony\Component\Security\Core\Exception\AccessDeniedException) {
            return 403;
        }

        return 500;
    }

    private static function extractHeaders(\Exception $exception)
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getHeaders();
        }

        return [];
    }

    private static function extractMessage(\Exception $exception, $debug)
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getMessage();
        }

        if ($debug) {
            // Expose exception message only in debug mode
            return $exception->getMessage();
        }

        if ($exception instanceof \Symfony\Component\Security\Core\Exception\AccessDeniedException) {
            return 'Access Denied';
        }

        return null;
    }
}
