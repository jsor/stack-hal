<?php

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestFormatValidator implements HttpKernelInterface
{
    private $app;
    private $acceptableFormats;

    public function __construct(HttpKernelInterface $app, callable $acceptableFormats = null)
    {
        $this->app = $app;
        $this->acceptableFormats = $acceptableFormats ?: [
            'json' => ['application/hal+json', 'application/json'],
            'xml' => ['application/hal+xml', 'application/xml']
        ];
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $format = $request->attributes->get('_format'); // Might be set via Negotiation middleware

        if (!isset($this->acceptableFormats[$format])) {
            $mimeType = $request->attributes->get('_mime_type'); // Might be set via Negotiation middleware
            $acceptableMimeTypes = call_user_func_array('array_merge', array_values($this->acceptableFormats));

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

        return $this->app->handle($request, $type, $catch);
    }
}
