<?php

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\EventListener\ResponseConversionListener;
use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\RequestFormatValidator;
use Jsor\Stack\Hal\ResponseConverter;
use Nocarrier\Hal;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Tests\Fixtures\KernelForTest;

class KernelTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_intercepts_not_acceptable_format()
    {
        $kernel = new KernelForTest('test', true);

        $app = new RequestFormatValidator($kernel);
        $app = new ExceptionConverter($app);

        $request = Request::create('/');
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
    }

    /** @test */
    public function it_converts_response_to_json()
    {
        $resolver = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');

        $resolver
            ->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(function () {
                return new Hal('/');
            }));

        $resolver
            ->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue([]));

        $dispatcher = new EventDispatcher();
        $httpKernel = new HttpKernel($dispatcher, $resolver);

        $kernel = new KernelForTest('test', true);
        $kernel->boot();
        $kernel->getContainer()->set('http_kernel', $httpKernel);

        $dispatcher->addSubscriber(new ResponseConversionListener());

        $app = new RequestFormatValidator($kernel);
        $app = new ExceptionConverter($app);

        $request = Request::create('/');
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
                ]
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_converts_exception_to_json()
    {
        $resolver = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');

        $resolver
            ->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(function () {
                throw new NotFoundHttpException();
            }));

        $resolver
            ->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue([]));

        $dispatcher = new EventDispatcher();
        $httpKernel = new HttpKernel($dispatcher, $resolver);

        $kernel = new KernelForTest('test', true);
        $kernel->boot();
        $kernel->getContainer()->set('http_kernel', $httpKernel);

        $app = new RequestFormatValidator($kernel);
        $app = new ExceptionConverter($app);

        $request = Request::create('/exception');
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request)->prepare($request);

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
}
