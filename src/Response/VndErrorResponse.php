<?php

namespace Jsor\Stack\Hal\Response;

use Jsor\Stack\Hal\Exception\HalException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class VndErrorResponse extends HalResponse
{
    public function __construct(Hal $hal, $status = 500, $headers = [], $prettyPrint = true)
    {
        parent::__construct($hal, $status, $headers, $prettyPrint);

        $this->headers->set('Content-Type', 'application/vnd.error+json');
    }

    public static function create($hal = null, $status = 500, $headers = [], $prettyPrint = true)
    {
        return new static($hal, $status, $headers, $prettyPrint);
    }

    public static function fromException(\Exception $exception, $prettyPrint = true, $debug = false)
    {
        $statusCode = 500;
        $headers = [];
        $message = null;

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = $exception->getHeaders();
            $message = $exception->getMessage();
        } elseif ($exception instanceof \Symfony\Component\Security\Core\Exception\AccessDeniedException) {
            $statusCode = 403;
            $message = $exception->getMessage();
        } elseif ($debug) {
            // Expose exception message only in debug mode
            $message = $exception->getMessage();
        }

        if ($exception instanceof HalException) {
            $hal = $exception->getHal();
        } elseif ($message) {
            $hal = new Hal(null, ['message' => $exception->getMessage()]);
        } else {
            $hal = new Hal();
        }

        $data = $hal->getData();

        if ((!isset($data['message']) || '' === $data['message']) &&
            isset(Response::$statusTexts[$statusCode])) {
            $data['message'] = Response::$statusTexts[$statusCode];
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
}
