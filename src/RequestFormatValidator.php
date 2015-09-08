<?php

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestFormatValidator implements HttpKernelInterface
{
    private $app;
    private $acceptableFormats;

    public function __construct(
        HttpKernelInterface $app,
        array $acceptableFormats = null
    ) {
        $this->app = $app;
        $this->acceptableFormats = $acceptableFormats;
    }

    public function handle(
        Request $request,
        $type = HttpKernelInterface::MASTER_REQUEST,
        $catch = true
    ) {
        $response = static::intercept($request, $this->acceptableFormats);

        if ($response instanceof Response) {
            return $response;
        }

        return $this->app->handle($request, $type, $catch);
    }

    public static function intercept(
        Request $request,
        array $acceptableFormats = null
    ) {
        $acceptableFormats = $acceptableFormats ?: [
            'json' => ['application/hal+json', 'application/json', 'application/x-json'],
            'xml' => ['application/hal+xml', 'text/xml', 'application/xml', 'application/x-xml']
        ];

        $format = $request->getRequestFormat(null);

        if ($format && isset($acceptableFormats[$format])) {
            return;
        }

        $acceptableMimeTypes = call_user_func_array(
            'array_merge',
            array_values($acceptableFormats)
        );

        // Might be set via Negotiation middleware
        $mimeType = $request->attributes->get('_mime_type');

        if ($mimeType) {
            return new Response(
                sprintf(
                    'Mime type "%s" is not supported. Supported mime types are: %s.',
                    $mimeType,
                    implode(', ', $acceptableMimeTypes)
                ),
                406,
                [
                    'Content-Type' => 'text/plain'
                ]
            );
        }

        if (!$format) {
            return new Response(
                sprintf(
                    'Could not detect supported mime type. Supported mime types are: %s.',
                    implode(', ', $acceptableMimeTypes)
                ),
                406,
                [
                    'Content-Type' => 'text/plain'
                ]
            );
        }

        return new Response(
            sprintf(
                'Format "%s" is not supported. Supported mime types are: %s.',
                $format,
                implode(', ', $acceptableMimeTypes)
            ),
            406,
            [
                'Content-Type' => 'text/plain'
            ]
        );
    }
}
