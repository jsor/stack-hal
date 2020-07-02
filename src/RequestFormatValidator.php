<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RequestFormatValidator implements HttpKernelInterface
{
    private $app;
    private $acceptableFormats;
    private $exclude;

    /**
     * @param array|string|null $exclude
     */
    public function __construct(
        HttpKernelInterface $app,
        array $acceptableFormats = null,
        $exclude = null
    ) {
        $this->app = $app;
        $this->acceptableFormats = $acceptableFormats;
        $this->exclude = $exclude;
    }

    public function handle(
        Request $request,
        $type = HttpKernelInterface::MASTER_REQUEST,
        $catch = true
    ) {
        $response = self::intercept(
            $request,
            $this->acceptableFormats,
            $this->exclude
        );

        if ($response instanceof Response) {
            return $response;
        }

        return $this->app->handle($request, $type, $catch);
    }

    /**
     * @param array|string|null $exclude
     */
    public static function intercept(
        Request $request,
        array $acceptableFormats = null,
        $exclude = null
    ): ?Response {
        $acceptableFormats = $acceptableFormats ?: [
            'json' => ['application/hal+json', 'application/json', 'application/x-json'],
            'xml' => ['application/hal+xml', 'text/xml', 'application/xml', 'application/x-xml'],
        ];

        $format = $request->getRequestFormat(null);

        if ($format && isset($acceptableFormats[$format])) {
            return null;
        }

        if (self::isExcluded($request, $exclude)) {
            return null;
        }

        $acceptableMimeTypes = array_merge(...\array_values($acceptableFormats));

        // Might be set via Negotiation middleware
        $mimeType = $request->attributes->get('_mime_type');

        if (!$mimeType) {
            $mimeType = \implode(', ', $request->getAcceptableContentTypes());
        }

        if ($mimeType) {
            return new Response(
                \sprintf(
                    'Mime type%s "%s" %s not supported. Supported mime types are: %s.',
                    false !== \strpos($mimeType, ',') ? 's' : '',
                    $mimeType,
                    false !== \strpos($mimeType, ',') ? 'are' : 'is',
                    \implode(', ', $acceptableMimeTypes)
                ),
                406,
                [
                    'Content-Type' => 'text/plain',
                ]
            );
        }

        if (!$format) {
            return new Response(
                \sprintf(
                    'Could not detect supported mime type. Supported mime types are: %s.',
                    \implode(', ', $acceptableMimeTypes)
                ),
                406,
                [
                    'Content-Type' => 'text/plain',
                ]
            );
        }

        return new Response(
            \sprintf(
                'Format "%s" is not supported. Supported mime types are: %s.',
                $format,
                \implode(', ', $acceptableMimeTypes)
            ),
            406,
            [
                'Content-Type' => 'text/plain',
            ]
        );
    }

    /**
     * @param array|string|null $exclude
     */
    private static function isExcluded(Request $request, $exclude): bool
    {
        if (!$exclude) {
            return false;
        }

        if (!\is_array($exclude) || 0 !== \key($exclude)) {
            $exclude = [$exclude];
        }

        $requestMatchers = \array_map([__CLASS__, 'createRequestMatcher'], $exclude);

        /** @var RequestMatcherInterface $requestMatcher */
        foreach ($requestMatchers as $requestMatcher) {
            if ($requestMatcher->matches($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param RequestMatcherInterface|array|string $arguments
     */
    private static function createRequestMatcher(
        $arguments
    ): RequestMatcherInterface {
        if ($arguments instanceof RequestMatcherInterface) {
            return $arguments;
        }

        if (!\is_array($arguments)) {
            return new RequestMatcher($arguments);
        }

        $arguments = \array_replace([
            'path' => null,
            'host' => null,
            'methods' => null,
            'ips' => null,
            'attributes' => [],
            'schemes' => null,
        ], $arguments);

        return new RequestMatcher(
            $arguments['path'],
            $arguments['host'],
            $arguments['methods'],
            $arguments['ips'],
            $arguments['attributes'],
            $arguments['schemes']
        );
    }
}
