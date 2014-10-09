<?php

namespace Jsor\Stack\Hal;

use Jsor\Stack\Hal\Configurator\ConfiguratorInterface;
use Nocarrier\Hal;
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
    private $factory;
    private $prettyPrint;

    public function __construct(HttpKernelInterface $app,
                                callable $factory = null,
                                $prettyPrint = false,
                                ConfiguratorInterface $configurator = null)
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
        } catch (\Exception $exception) {
            if (!$catch) {
                throw $exception;
            }

            $response = static::convert($exception, $request, $this->factory, $this->prettyPrint);

            if ($response instanceof \Exception) {
                throw $response;
            }

            return $response;
        }
    }

    public static function convert(\Exception $exception, Request $request, callable $factory = null, $prettyPrint = false)
    {
        $format = $request->attributes->get('_format');

        if (!in_array($format, ['json', 'xml'])) {
            return $exception;
        }

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
        $hal = call_user_func($factory, $message, $statusCode, $headers);

        switch ($format) {
            case 'xml':
                return new Response($hal->asXml($prettyPrint), $statusCode, array_merge(
                    $headers,
                    ['Content-Type' => 'application/vnd.error+xml']
                ));

            default:
                return new Response($hal->asJson($prettyPrint), $statusCode, array_merge(
                    $headers,
                    ['Content-Type' => 'application/vnd.error+json']
                ));
        }
    }
}
