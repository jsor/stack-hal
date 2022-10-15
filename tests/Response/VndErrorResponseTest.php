<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

use Exception;
use Jsor\Stack\Hal\Exception\ErrorException;
use Jsor\Stack\Hal\Exception\HalException;
use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VndErrorResponseTest extends TestCase
{
    use HalResponseTestCase;

    protected function provideResponse(Hal $hal = null): HalResponse
    {
        return new VndErrorResponse($hal ?: new Hal());
    }

    /** @test */
    public function it_sets_default_content_type_header(): void
    {
        $response = $this->provideResponse();
        $this->assertSame('application/vnd.error+json', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_sets_content_type_header_depending_on_request_format(): void
    {
        $response = $this->provideResponse();

        $request = new Request();
        $request->setRequestFormat('xml');
        $response->prepare($request);
        $this->assertSame('application/vnd.error+xml', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_accepts_hal_exception(): void
    {
        $exception = new ErrorException(['Error'], 'Error', 100);

        $response = VndErrorResponse::fromThrowable($exception);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Error',
                    'logref' => 100,
                    '_embedded' => [
                            'errors' => [
                                    [
                                        'message' => 'Error',
                                    ],
                                ],
                        ],
                ],
            ),
            $response->getContent(),
        );
    }

    /** @test */
    public function it_hides_message_from_unknown_exceptions_by_default(): void
    {
        $exception = new Exception('Unknown Error');

        $response = VndErrorResponse::fromThrowable($exception);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Internal Server Error',
                ],
            ),
            $response->getContent(),
        );
    }

    /** @test */
    public function it_exposes_message_from_unknown_exceptions_in_debug_mode(): void
    {
        $exception = new Exception('Unknown Error');

        $response = VndErrorResponse::fromThrowable($exception, true, true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Unknown Error',
                ],
            ),
            $response->getContent(),
        );
    }

    /** @test */
    public function it_exposes_message_from_hal_exceptions_in_debug_mode(): void
    {
        $exception = new EmptyHalException('Message');

        $response = VndErrorResponse::fromThrowable($exception, true, true);

        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Message',
                ],
            ),
            $response->getContent(),
        );
    }
}

final class EmptyHalException extends Exception implements HalException
{
    public function getHal(): Hal
    {
        return new Hal();
    }
}
