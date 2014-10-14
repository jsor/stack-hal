<?php

namespace Jsor\Stack\Hal\Response;

use Jsor\Stack\Hal\Exception\HalException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class VndErrorResponse extends HalResponse
{
    public function __construct(Hal $hal, $status = 500, $headers = array(), $prettyPrint = false, $debug = false)
    {
        parent::__construct($hal, $status, $headers, $prettyPrint);

        $this->headers->set('Content-Type', 'application/vnd.error+json');
    }

    public static function create($hal = null, $status = 500, $headers = array(), $prettyPrint = false, $debug = false)
    {
        return new static($hal, $status, $headers, $prettyPrint, $debug);
    }

    public static function fromException(\Exception $exception, $prettyPrint = false, $debug = false)
    {
        $statusCode = 500;
        $headers = [];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        }

        if ($exception instanceof HalException) {
            $hal = $exception->getHal();
        } elseif ($debug || $exception instanceof HttpExceptionInterface) {
            $hal = new Hal(null, ['message' => $exception->getMessage()]);
        } else {
            // Discard exception messages from exceptions not implementing
            // HttpExceptionInterface (if $debug is false)
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
