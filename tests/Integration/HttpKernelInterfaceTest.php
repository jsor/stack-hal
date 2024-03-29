<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\RequestFormatNegotiator;
use Jsor\Stack\Hal\RequestFormatValidator;
use Jsor\Stack\Hal\Response\HalResponse;
use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class HttpKernelInterfaceTest extends TestCase
{
    /** @test */
    public function it_intercepts_not_acceptable_format(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestFormatNegotiator($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_converts_response_to_json(): void
    {
        $hal = new HalResponse(new Hal('/'));

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($hal);

        $app = new RequestFormatNegotiator($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

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
                ],
            ),
            $response->getContent(),
        );
    }

    /** @test */
    public function it_converts_response_to_xml(): void
    {
        $hal = new HalResponse(new Hal('/'));

        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn($hal);

        $app = new RequestFormatNegotiator($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/hal+xml', $response->headers->get('Content-Type'));
        $this->assertXmlStringEqualsXmlString(
            '<resource href="/"/>',
            $response->getContent(),
        );
    }

    /** @test */
    public function it_converts_exception_to_json(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException()));

        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->once())
            ->method('error');

        $app = new RequestFormatNegotiator($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app, $logger);

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/vnd.error+json', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Not Found',
                ],
            ),
            $response->getContent(),
        );
    }

    /** @test */
    public function it_converts_exception_to_xml(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        $kernel
            ->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new NotFoundHttpException()));

        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->once())
            ->method('error');

        $app = new RequestFormatNegotiator($kernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app, $logger);

        $request = new Request();
        $request->attributes->set('_format', 'xml');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/vnd.error+xml', $response->headers->get('Content-Type'));
        $this->assertXmlStringEqualsXmlString(
            '<resource><message>Not Found</message></resource>',
            $response->getContent(),
        );
    }
}
