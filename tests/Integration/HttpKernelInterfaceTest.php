<?php

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\RequestFormatValidator;
use Jsor\Stack\Hal\ResponseConverter;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group no-deps
 */
class HttpKernelInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_intercepts_not_acceptable_format()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/hal+xml, application/xml.', $response->getContent());
    }

    /** @test */
    public function it_converts_response_to_json()
    {
        $hal = new Hal('/');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

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
    public function it_converts_response_to_xml()
    {
        $hal = new Hal('/');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/hal+xml', $response->headers->get('Content-Type'));
        $this->assertXmlStringEqualsXmlString(
            '<resource href="/"/>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_converts_exception_to_json()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException()));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/vnd.error+json', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Not Found'
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_converts_exception_to_xml()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException()));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/vnd.error+xml', $response->headers->get('Content-Type'));
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Not Found</message></resource>',
            $response->getContent()
        );
    }
}
