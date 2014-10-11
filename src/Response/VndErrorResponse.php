<?php

namespace Jsor\Stack\Hal\Response;

use Jsor\Stack\Hal\Exception\HalException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class VndErrorResponse extends HalResponse
{
    protected $hal;
    protected $requestFormat;
    protected $prettyPrint;

    public function __construct(Hal $hal, $status = 500, $headers = array(), $prettyPrint = false)
    {
        parent::__construct($hal, $status, $headers, $prettyPrint);

        $this->headers->set('Content-Type', 'application/vnd.error+json');
    }

    public static function create($hal = null, $status = 500, $headers = array(), $prettyPrint = false)
    {
        return new static($hal, $status, $headers, $prettyPrint);
    }

    public static function fromException(\Exception $exception, $prettyPrint = false)
    {
        $statusCode = 500;
        $headers = [];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        }

        if ($exception instanceof HalException) {
            $hal = $exception->getHal();
        } elseif ($exception instanceof HttpExceptionInterface) {
            $hal = new Hal(null, ['message' => $exception->getMessage()]);
        } else {
            // Discard exception messages from exceptions
            // not implementing HttpExceptionInterface
            $hal = new Hal();
        }

        $data = $hal->getData();

        if ((!isset($data['message']) || '' === $data['message']) &&
            isset(Response::$statusTexts[$statusCode])) {
            $data['message'] = Response::$statusTexts[$statusCode];
            $hal->setData($data);
        }

        return static::create($hal, $statusCode, $headers, $prettyPrint);
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
