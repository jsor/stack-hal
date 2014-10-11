<?php

namespace Jsor\Stack\Hal;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group no-deps
 */
class ResponseConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_returns_original_response_for_invalid_format()
    {
        $expectedResponse = new Response();

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new ResponseConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_returns_original_response_if_response_is_not_a_hal_instance()
    {
        $expectedResponse = new Response();

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new ResponseConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_returns_a_valid_hal_json_response()
    {
        $hal = new Hal('/');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/hal+json', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    '_links' => [
                        'self' => [
                            'href' => '/',
                        ],
                    ],
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_returns_a_valid_hal_xml_response()
    {
        $hal = new Hal('/');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/hal+xml', $response->headers->get('Content-Type'));
        $this->assertXmlStringEqualsXmlString(
            '<resource href="/"/>',
            $response->getContent()
        );
    }
}
