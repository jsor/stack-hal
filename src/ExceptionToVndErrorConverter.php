<?php

namespace Jsor\Stack\Hal;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionToVndErrorConverter implements HttpKernelInterface
{
    private $app;
    private $factory;
    private $prettyPrint;

    public function __construct(HttpKernelInterface $app, callable $factory = null, $prettyPrint = false)
    {
        if (!$factory) {
            $factory = function ($message, $statusCode) {
                return new Hal(null, ['message' => $message]);
            };
        }

        $this->app = $app;
        $this->factory = $factory;
        $this->prettyPrint = (bool) $prettyPrint;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        try {
            return $this->app->handle($request, $type, false);
        } catch (\Exception $e) {
            if (!$catch) {
                throw $e;
            }

            $format = $request->attributes->get('_format');

            if (!in_array($format, ['json', 'xml'])) {
                throw $e;
            }

            return $this->createResponseFromException($e, $format);
        }
    }

    private function createResponseFromException(\Exception $exception, $format)
    {
        $statusCode = 500;
        $message = '';
        $headers = [];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
            $headers = $exception->getHeaders();
        }

        if (!$message && isset(Response::$statusTexts[$statusCode])) {
            $message = Response::$statusTexts[$statusCode];
        }

        /* @var $hal Hal */
        $hal = call_user_func($this->factory, $message, $statusCode);

        switch ($format) {
            case 'xml':
                return new Response($hal->asXml($this->prettyPrint), $statusCode, array_merge(
                    $headers,
                    ['Content-Type' => 'application/vnd.error+xml']
                ));

            default:
                return new Response($hal->asJson($this->prettyPrint), $statusCode, array_merge(
                    $headers,
                    ['Content-Type' => 'application/vnd.error+json']
                ));
        }
    }
}
