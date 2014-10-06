<?php

namespace Jsor\Stack\Hal;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseConverter implements HttpKernelInterface
{
    private $app;
    private $prettyPrint;

    public function __construct(HttpKernelInterface $app, $prettyPrint = false)
    {
        $this->app = $app;
        $this->prettyPrint = (bool) $prettyPrint;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $hal = $this->app->handle($request, $type, $catch);

        if (!$hal instanceof Hal) {
            return $hal;
        }

        $format = $request->attributes->get('_format');

        if (!in_array($format, ['json', 'xml'])) {
            // Attention, we return the Nocarrier\Hal instance here!
            // If you don't use RequestFormatValidator (which should have
            // prevented invocation of this middelware), you need to take care
            // by yourself to turn the Nocarrier\Hal instance into a valid
            // Response object.
            return $hal;
        }

        switch ($format) {
            case 'xml':
                return new Response($hal->asXml($this->prettyPrint), 200, [
                    'Content-Type' => 'application/hal+xml'
                ]);

            default:
                return new Response($hal->asJson($this->prettyPrint), 200, [
                    'Content-Type' => 'application/hal+json'
                ]);
        }
    }
}
