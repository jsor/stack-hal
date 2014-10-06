<?php

namespace Jsor\Stack\Hal;

use Nocarrier\Hal;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ResponseConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_returns_original_response_for_invalid_format()
    {
        $hal = new Hal();

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request);

        $this->assertSame($hal, $response);
    }

    /** @test */
    public function it_returns_original_response_if_response_is_not_a_hal_instance()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue('success'));

        $app = new ResponseConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame('success', $response);
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

        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
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

        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertXmlStringEqualsXmlString(
            '<resource href="/"/>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_converts_for_silex()
    {
        $silex = new Application();
        $silex['debug'] = true;
        $silex->get('/', function () {
            return new Hal('/');
        });

        $app = new ResponseConverter($silex);

        $request = Request::create('/');
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
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
    public function it_converts_for_http_kernel()
    {
        $silex = new Application();
        $silex['debug'] = true;
        $silex->get('/', function () {
            return new Hal('/');
        });
        $silex->boot();
        $silex->flush();

        $app = new ResponseConverter($silex['kernel']);

        $request = Request::create('/');
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
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
}
