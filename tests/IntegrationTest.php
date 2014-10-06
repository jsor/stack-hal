<?php

namespace Jsor\Stack\Hal;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_returns_406_for_not_acceptable_format()
    {
        $hal = new Hal();

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionToVndErrorConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/hal+xml, application/xml.', $response->getContent());
    }

    /** @test */
    public function it_converts_hal_instance_to_json()
    {
        $hal = new Hal('/');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionToVndErrorConverter($app);

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
    public function it_converts_hal_instance_to_xml()
    {
        $hal = new Hal('/');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($hal));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionToVndErrorConverter($app);

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
    public function it_converts_exception_to_json()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException()));

        $app = new ResponseConverter($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionToVndErrorConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Not Found',
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
        $app = new ExceptionToVndErrorConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Not Found</message></resource>',
            $response->getContent()
        );
    }
}
