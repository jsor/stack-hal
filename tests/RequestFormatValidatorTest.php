<?php

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;

class RequestFormatValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_accepts_default_json_format()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue('success'));

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

        $this->assertSame('success', $response);
    }

    /** @test */
    public function it_accepts_default_xml_format()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue('success'));

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request);

        $this->assertSame('success', $response);
    }

    /** @test */
    public function it_returns_406_for_null_format()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();

        $response = $app->handle($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('content-type'));
        $this->assertSame('Could not detect supported mime type. Supported mime types are: application/hal+json, application/json, application/hal+xml, application/xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_not_acceptable_format()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('content-type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/hal+xml, application/xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_not_acceptable_format_with_mime_type()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');
        $request->attributes->set('_mime_type', 'text/html');

        $response = $app->handle($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('content-type'));
        $this->assertSame('Mime type "text/html" is not supported. Supported mime types are: application/hal+json, application/json, application/hal+xml, application/xml.', $response->getContent());
    }
}
