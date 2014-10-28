<?php

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        $response = $app->handle($request)->prepare($request);

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

        $response = $app->handle($request)->prepare($request);

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

        $response = $app->handle($request)->prepare($request);

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
    public function it_serializes_access_denied_exception_to_json()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new AccessDeniedException('Forbidden')));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Forbidden',
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

        $response = $app->handle($request)->prepare($request);

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

        $response = $app->handle($request)->prepare($request);

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

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Resource not found</message></resource>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_serializes_access_denied_exception_to_xml()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new AccessDeniedException('Forbidden')));

        $app = new ExceptionConverter($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Forbidden</message></resource>',
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

        $response = $app->handle($request)->prepare($request);

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
    public function it_exposes_standard_exception_message_when_debug_is_true()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \Exception('Custom error message')));

        $app = new ExceptionConverter($kernel, false, true);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Custom error message',
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
}
