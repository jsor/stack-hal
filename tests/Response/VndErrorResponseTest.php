<?php

namespace Jsor\Stack\Hal\Response;

use Jsor\Stack\Hal\Exception\ErrorException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;

class VndErrorResponseTest extends \PHPUnit_Framework_TestCase
{
    use ResponseTestCase;

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
}
