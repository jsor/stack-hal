<?php

namespace Jsor\Stack\Hal;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @group no-deps
 */
class ExceptionConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_serializes_exception_to_json()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \Exception()));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Internal Server Error',
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_serializes_http_exception_with_default_message_to_json()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException()));

        $app = new ExceptionConverter($kernel);

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
    public function it_serializes_http_exception_with_custom_message_to_json()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException('Resource not found')));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Resource not found',
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_serializes_exception_to_xml()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \Exception()));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Internal Server Error</message></resource>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_serializes_http_exception_with_default_message_to_xml()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException()));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Not Found</message></resource>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_serializes_http_exception_with_custom_message_to_xml()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException('Resource not found')));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Resource not found</message></resource>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_discards_standard_exception_message()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \Exception('Error')));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Internal Server Error',
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_rethrows_exception_if_catch_is_false()
    {
        $this->setExpectedException('\Exception', 'Error');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \Exception('Error')));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $app->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
    }

    /** @test */
    public function it_rethrows_exception_for_invalid_request_format()
    {
        $this->setExpectedException('\Exception', 'Error');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \Exception('Error')));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $app->handle($request);
    }

    /** @test */
    public function it_uses_hal_factory()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \Exception()));

        $app = new ExceptionConverter($kernel, function ($message) {
            return new Hal(null, ['message' => 'Error: ' . $message]);
        });

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Error: Internal Server Error',
                ]
            ),
            $response->getContent()
        );
    }
}
