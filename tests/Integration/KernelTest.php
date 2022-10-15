<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\EventListener\ExceptionConversionListener;
use Jsor\Stack\Hal\EventListener\RequestFormatNegotiationListener;
use Jsor\Stack\Hal\EventListener\RequestFormatValidationListener;
use Jsor\Stack\Hal\EventListener\ResponseConversionListener;
use Jsor\Stack\Hal\Fixtures\KernelForTest;
use Jsor\Stack\Hal\Fixtures\TestHttpKernel;
use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class KernelTest extends TestCase
{
    /** @test */
    public function it_intercepts_not_acceptable_format(): void
    {
        $dispatcher = new EventDispatcher();
        $httpKernel = new TestHttpKernel($dispatcher);

        $kernel = new KernelForTest('test', true);
        $kernel->boot();
        $kernel->getContainer()->set('http_kernel', $httpKernel);

        $dispatcher->addSubscriber(new RequestFormatNegotiationListener());
        $dispatcher->addSubscriber(new RequestFormatValidationListener());
        $dispatcher->addSubscriber(new ResponseConversionListener());
        $dispatcher->addSubscriber(new ExceptionConversionListener(null, true, true));

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'text/html',
        ]);
        $request->attributes->set('_format', 'html');

        $response = $kernel->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Mime type "text/html" is not supported. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_converts_response_to_json(): void
    {
        $dispatcher = new EventDispatcher();
        $httpKernel = new TestHttpKernel($dispatcher, static function () {
            return new Hal('/');
        });

        $kernel = new KernelForTest('test', true);
        $kernel->boot();
        $kernel->getContainer()->set('http_kernel', $httpKernel);

        $dispatcher->addSubscriber(new RequestFormatNegotiationListener());
        $dispatcher->addSubscriber(new RequestFormatValidationListener());
        $dispatcher->addSubscriber(new ResponseConversionListener());
        $dispatcher->addSubscriber(new ExceptionConversionListener());

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $request->attributes->set('_format', 'json');

        $response = $kernel->handle($request)->prepare($request);

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
    public function it_converts_exception_to_json(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->once())
            ->method('error');

        $dispatcher = new EventDispatcher();
        $httpKernel = new TestHttpKernel($dispatcher, static function () {
            throw new NotFoundHttpException();
        });

        $kernel = new KernelForTest('test', true);
        $kernel->boot();
        $kernel->getContainer()->set('http_kernel', $httpKernel);

        $dispatcher->addSubscriber(new RequestFormatNegotiationListener());
        $dispatcher->addSubscriber(new RequestFormatValidationListener());
        $dispatcher->addSubscriber(new ResponseConversionListener());
        $dispatcher->addSubscriber(new ExceptionConversionListener($logger));

        $request = Request::create('/exception', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $request->attributes->set('_format', 'json');

        $response = $kernel->handle($request)->prepare($request);

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
}
