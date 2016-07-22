<?php

namespace Jsor\Stack\Hal;

use Negotiation\Accept;
use Negotiation\Negotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestFormatNegotiator implements HttpKernelInterface
{
    private $app;
    private $formats;
    private $priorities;

    private static $defaultFormats = [
        'application/hal+json' => 'json',
        'application/hal+xml' => 'xml',

        'application/json' => 'json',
        'application/x-json' => 'json',

        'text/xml' => 'xml',
        'application/xml' => 'xml',
        'application/x-xml' => 'xml'
    ];

    public function __construct(
        HttpKernelInterface $app,
        array $formats = null,
        array $priorities = null
    ) {
        $this->app        = $app;
        $this->formats    = $formats;
        $this->priorities = $priorities;
    }

    public function handle(
        Request $request,
        $type = HttpKernelInterface::MASTER_REQUEST,
        $catch = true
    ) {
        self::negotiate($request, $this->formats, $this->priorities);

        return $this->app->handle($request, $type, $catch);
    }

    public static function negotiate(
        Request $request,
        array $formats = null,
        array $priorities = null
    ) {
        if (null === $formats) {
            $formats = self::$defaultFormats;
        }

        $formats = array_change_key_case($formats, CASE_LOWER);

        if (null === $priorities) {
            $priorities = array_keys($formats);
        }

        $acceptHeader = $request->headers->get('Accept');

        if (!$acceptHeader) {
            return;
        }

        $negotiator = new Negotiator();

        /** @var Accept $accept */
        $accept = $negotiator->getBest($acceptHeader, $priorities);

        if (!$accept) {
            return;
        }

        $request->attributes->set('_mime_type', $accept->getValue());

        if (isset($formats[$accept->getType()])) {
            $request->attributes->set(
                '_format',
                $formats[$accept->getType()]
            );
        }
    }
}
