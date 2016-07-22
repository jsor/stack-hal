<?php

namespace Jsor\Stack\Hal\Response;

use Jsor\Stack\Hal\Exception\ErrorException;
use Jsor\Stack\Hal\Exception\HalException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;

class VndErrorResponseTest extends \PHPUnit_Framework_TestCase
{
    use HalResponseTestCase;

    protected function provideResponse(Hal $hal = null)
    {
        return VndErrorResponse::create($hal ?: new Hal());
    }

    /** @test */
    public function it_sets_default_content_type_header()
    {
        $response = $this->provideResponse();
        $this->assertSame('application/vnd.error+json', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_sets_content_type_header_depending_on_request_format()
    {
        $response = $this->provideResponse();

        $request = new Request();
        $request->setRequestFormat('xml');
        $response->prepare($request);
        $this->assertSame('application/vnd.error+xml', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_accepts_hal_exception()
    {
        $exception = new ErrorException(['Error'], 'Error', 100);

        $response = VndErrorResponse::fromException($exception);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Error',
                    'logref' => 100,
                    '_embedded' =>
                        [
                            'errors' =>
                                [
                                    [
                                        'message' => 'Error',
                                    ],
                                ],
                        ],
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_hides_message_from_unknown_exceptions_by_default()
    {
        $exception = new \Exception('Unknown Error');

        $response = VndErrorResponse::fromException($exception);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Internal Server Error'
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_exposes_message_from_unknown_exceptions_in_debug_mode()
    {
        $exception = new \Exception('Unknown Error');

        $response = VndErrorResponse::fromException($exception, true, true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Unknown Error'
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_exposes_message_from_hal_exceptions_in_debug_mode()
    {
        $exception = new EmptyHalException('Message');

        $response = VndErrorResponse::fromException($exception, true, true);

        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Message'
                ]
            ),
            $response->getContent()
        );
    }
}

class EmptyHalException extends \Exception implements HalException
{
    public function getHal()
    {
        return new Hal();
    }
}
