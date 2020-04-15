<?php

namespace Jsor\Stack\Hal\Response;

use Jsor\Stack\Hal\Exception\HalException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    public static function fromThrowable(
        \Throwable $throwable,
        $prettyPrint = true,
        $debug = false
    ) {
        $statusCode = self::extractStatus($throwable);
        $headers = self::extractHeaders($throwable);
        $message = self::extractMessage($throwable, $debug);

        if ($throwable instanceof HalException) {
            $hal = $throwable->getHal();
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

    private static function extractStatus(\Throwable $throwable)
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->getStatusCode();
        }

        if ($throwable instanceof AccessDeniedException) {
            return 403;
        }

        return 500;
    }

    private static function extractHeaders(\Throwable $throwable)
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->getHeaders();
        }

        return [];
    }

    private static function extractMessage(\Throwable $throwable, $debug)
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->getMessage();
        }

        if ($debug) {
            // Expose exception message only in debug mode
            return $throwable->getMessage();
        }

        if ($throwable instanceof AccessDeniedException) {
            return 'Access Denied';
        }

        return null;
    }
}
