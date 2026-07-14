<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\HostRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RequestFormatValidatorTest extends TestCase
{
    /** @test */
    public function it_accepts_default_hal_format(): void
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'hal');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_accepts_default_json_format(): void
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_accepts_default_xml_format(): void
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_returns_406_for_null_format(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=utf-8', $response->headers->get('content-type'));
        $this->assertSame('Could not detect supported mime type. Supported mime types are: application/hal+json, application/hal+xml, application/json, application/x-json, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_not_acceptable_format(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=utf-8', $response->headers->get('content-type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/hal+xml, application/json, application/x-json, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_not_acceptable_format_with_mime_type(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', 'html');
        $request->headers->set('Accept', 'text/html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=utf-8', $response->headers->get('content-type'));
        $this->assertSame('Mime type "text/html" is not supported. Supported mime types are: application/hal+json, application/hal+xml, application/json, application/x-json, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_returns_406_for_null_format_with_mime_type(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel);

        $request = new Request();
        $request->attributes->set('_format', null);
        $request->headers->set('Accept', 'text/html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=utf-8', $response->headers->get('content-type'));
        $this->assertSame('Mime type "text/html" is not supported. Supported mime types are: application/hal+json, application/hal+xml, application/json, application/x-json, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_ignores_with_exclude_as_string(): void
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $app = new RequestFormatValidator($kernel, [], '/ignore');

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_string(): void
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $app = new RequestFormatValidator($kernel, [], [
            '/ignore',
        ]);

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_request_matcher(): void
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $app = new RequestFormatValidator($kernel, [], [new PathRequestMatcher('/ignore')]);

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_ignores_with_exclude_as_array_with_multiple_request_matchers(): void
    {
        $expectedResponse = new Response();

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $app = new RequestFormatValidator($kernel, [], [
            new HostRequestMatcher('example\.com'),
            new PathRequestMatcher('/ignore'),
        ]);

        $request = Request::create('/ignore');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function it_intercepts_with_non_matching_exclude(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatValidator($kernel, [], '/ignore');

        $request = Request::create('/not-ignore');

        $app->handle($request)->prepare($request);
    }
}
