<?php

namespace Jsor\Stack\Hal;

use Jsor\Stack\Hal\ResponseConverter\GuessingHttpKernelConfigurator;
use Jsor\Stack\Hal\ResponseConverter\HttpKernelConfigurator;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseConverter implements HttpKernelInterface
{
    private $app;
    private $prettyPrint;

    public function __construct(HttpKernelInterface $app,
                                $prettyPrint = false,
                                HttpKernelConfigurator $httpKernelConfigurator = null)
    {
        $this->app = $app;
        $this->prettyPrint = (bool) $prettyPrint;

        if (!$httpKernelConfigurator) {
            $httpKernelConfigurator = new GuessingHttpKernelConfigurator();
        }

        $httpKernelConfigurator->configureHttpKernel($app, $this->prettyPrint);
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        return static::convert(
            $this->app->handle($request, $type, $catch),
            $request,
            $this->prettyPrint
        );
    }

    public static function convert($response, Request $request, $prettyPrint = false)
    {
        if (!$response instanceof Hal) {
            return $response;
        }

        $format = $request->attributes->get('_format');

        if (!in_array($format, ['json', 'xml'])) {
            // Attention, we return the Nocarrier\Hal instance here!
            // If you don't use RequestFormatValidator (which should have
            // prevented invocation of this middelware), you need to take care
            // by yourself to turn the Nocarrier\Hal instance into a valid
            // Response object.
            return $response;
        }

        switch ($format) {
            case 'xml':
                return new Response($response->asXml($prettyPrint), 200, [
                    'Content-Type' => 'application/hal+xml'
                ]);

            default:
                return new Response($response->asJson($prettyPrint), 200, [
                    'Content-Type' => 'application/hal+json'
                ]);
        }
    }
}
