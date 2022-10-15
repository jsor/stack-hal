<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

use function call_user_func;
use function in_array;
use function is_array;
use function is_callable;

/**
 * Adapted from the FOSRestBundle BodyListener.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
final class RequestBodyDecoder implements HttpKernelInterface
{
    private HttpKernelInterface $app;
    private ?array $decoders;

    /**
     * @param callable[] $decoders
     */
    public function __construct(
        HttpKernelInterface $app,
        array $decoders = null,
    ) {
        $this->app = $app;
        $this->decoders = $decoders;
    }

    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true,
    ): Response {
        try {
            self::decode($request, $this->decoders);
        } catch (BadRequestHttpException $exception) {
            if (!$catch) {
                throw $exception;
            }

            return new Response(
                $exception->getMessage(),
                Response::HTTP_BAD_REQUEST,
            );
        }

        return $this->app->handle($request, $type, $catch);
    }

    /**
     * @param callable[] $decoders
     */
    public static function decode(
        Request $request,
        array $decoders = null,
    ): void {
        if (null === $decoders) {
            $decoders = [
                'json' => static function ($content) {
                    return (new JsonEncoder())->decode($content, 'json');
                },
                'xml' => static function ($content) {
                    return (new XmlEncoder())->decode($content, 'xml');
                },
            ];
        }

        if (!self::isDecodeable($request)) {
            return;
        }

        $contentType = $request->headers->get('Content-Type');

        $format = null === $contentType
            ? $request->getRequestFormat()
            : $request->getFormat($contentType);

        if (!$format || !isset($decoders[$format])) {
            return;
        }

        if (!is_callable($decoders[$format])) {
            return;
        }

        $content = $request->getContent();

        if (!$content) {
            return;
        }

        try {
            $data = call_user_func($decoders[$format], $content);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Invalid ' . $format . ' message received', $e);
        }

        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid ' . $format . ' message received');
        }

        $request->request->replace($data);
    }

    private static function isDecodeable(Request $request): bool
    {
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return !self::isFormRequest($request);
    }

    private static function isFormRequest(Request $request): bool
    {
        if (!$request->headers->has('Content-Type')) {
            return false;
        }

        $contentTypeParts = explode(';', (string) $request->headers->get('Content-Type'));

        if (!isset($contentTypeParts[0]) || '' === trim($contentTypeParts[0])) {
            return false;
        }

        return in_array(
            strtolower($contentTypeParts[0]),
            ['multipart/form-data', 'application/x-www-form-urlencoded'],
            true,
        );
    }
}
