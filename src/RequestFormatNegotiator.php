<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal;

use Negotiation\Accept;
use Negotiation\Negotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RequestFormatNegotiator implements HttpKernelInterface
{
    private HttpKernelInterface $app;
    private ?array $formats;
    private ?array $priorities;

    private const DEFAULT_FORMATS = [
        'json' => ['application/hal+json', 'application/json', 'application/x-json'],
        'xml' => ['application/hal+xml', 'text/xml', 'application/xml', 'application/x-xml'],
    ];

    public function __construct(
        HttpKernelInterface $app,
        array $formats = null,
        array $priorities = null,
    ) {
        $this->app = $app;
        $this->formats = $formats;
        $this->priorities = $priorities;
    }

    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true,
    ): Response {
        self::negotiate($request, $this->formats, $this->priorities);

        return $this->app->handle($request, $type, $catch);
    }

    public static function negotiate(
        Request $request,
        array $formats = null,
        array $priorities = null,
    ): void {
        $formats = $formats ?: self::DEFAULT_FORMATS;

        self::extendRequestFormats($request, $formats);

        if (null === $priorities) {
            $priorities = self::buildPrioritiesFromFormats($formats);
        }

        $acceptHeader = $request->headers->get('Accept');

        if (!$acceptHeader) {
            return;
        }

        $negotiator = new Negotiator();

        /** @var Accept|null $accept */
        $accept = $negotiator->getBest($acceptHeader, $priorities);

        if (!$accept) {
            return;
        }

        $request->attributes->set('_mime_type', $accept->getValue());
        $request->setRequestFormat($request->getFormat($accept->getType()));
    }

    private static function extendRequestFormats(
        Request $request,
        array $formats,
    ): void {
        foreach ($formats as $format => $mimeTypes) {
            $allMimeTypes = array_merge(
                $mimeTypes,
                Request::getMimeTypes($format),
            );

            $request->setFormat($format, array_unique($allMimeTypes));
        }
    }

    private static function buildPrioritiesFromFormats(array $formats): array
    {
        $priorities = [];

        foreach ($formats as $types) {
            $priorities = array_merge($priorities, $types);
        }

        return $priorities;
    }
}
