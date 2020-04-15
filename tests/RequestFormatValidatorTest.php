<?php

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Response;

class RequestFormatValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function it_accepts_default_json_format()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_accepts_default_xml_format()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_returns_406_for_null_format()
    {
        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertSame('Could not detect supported mime type. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_not_acceptable_format()
    {
        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_not_acceptable_format_with_mime_type()
    {
        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');
        $request->attributes->set('_mime_type', 'text/html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertSame('Mime type "text/html" is not supported. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_null_format_with_mime_type()
    {
        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', null);
        $request->attributes->set('_mime_type', 'text/html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertSame('Mime type "text/html" is not supported. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_ignores_with_exclude_as_string()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel, [], '/ignore');

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_string()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel, [], [
            '/ignore'
        ]);

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_arguments()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel, [], [
            'host' => 'example\.com'
        ]);

        $request = Request::create('http://example.com');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_multiple_arguments()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel, [], [
            ['host' => 'example\.com'],
            ['path' => '/ignore']
        ]);

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_request_matcher()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel, [], new RequestMatcher('/ignore'));

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_multiplerequest_matcher()
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = new RequestFormatValidator($kernel, [], [
            new RequestMatcher(null, 'example\.com'),
            new RequestMatcher('/ignore')
        ]);

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_intercepts_with_non_matching_exclude()
    {
        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel, [], '/ignore');

        $request = Request::create('/not-ignore');

        $response = $app->handle($request)->prepare($request);
    }
}
