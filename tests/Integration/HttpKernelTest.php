<?php

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\EventListener\ExceptionConversionListener;
use Jsor\Stack\Hal\EventListener\RequestFormatNegotiationListener;
use Jsor\Stack\Hal\EventListener\RequestFormatValidationListener;
use Jsor\Stack\Hal\EventListener\ResponseConversionListener;
use Nocarrier\Hal;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;

class HttpKernelTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_intercepts_not_acceptable_format()
    {
        $resolver = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');

        $resolver
            ->expects($this->never())
            ->method('getController');

        $resolver
            ->expects($this->never())
            ->method('getArguments');

        $dispatcher = new EventDispatcher();
        $httpKernel = new HttpKernel($dispatcher, $resolver);

        $dispatcher->addSubscriber(new RequestFormatNegotiationListener());
        $dispatcher->addSubscriber(new RequestFormatValidationListener());
        $dispatcher->addSubscriber(new ResponseConversionListener());
        $dispatcher->addSubscriber(new ExceptionConversionListener());

        $request = Request::create('/exception', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'text/html'
        ]);
        $request->attributes->set('_format', 'html');

        $response = $httpKernel->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Mime type "text/html" is not supported. Supported mime types are: application/hal+json, application/json, application/x-json, application/hal+xml, text/xml, application/xml, application/x-xml.', $response->getContent());
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

        $dispatcher->addSubscriber(new RequestFormatNegotiationListener());
        $dispatcher->addSubscriber(new RequestFormatValidationListener());
        $dispatcher->addSubscriber(new ResponseConversionListener());
        $dispatcher->addSubscriber(new ExceptionConversionListener());

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json'
        ]);
        $request->attributes->set('_format', 'json');

        $response = $httpKernel->handle($request)->prepare($request);

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

        $logger = $this->getMock('Psr\Log\LoggerInterface');

        $logger
            ->expects($this->once())
            ->method('error');

        $dispatcher = new EventDispatcher();
        $httpKernel = new HttpKernel($dispatcher, $resolver);

        $dispatcher->addSubscriber(new RequestFormatNegotiationListener());
        $dispatcher->addSubscriber(new RequestFormatValidationListener());
        $dispatcher->addSubscriber(new ResponseConversionListener());
        $dispatcher->addSubscriber(new ExceptionConversionListener($logger));

        $request = Request::create('/exception', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json'
        ]);
        $request->attributes->set('_format', 'json');

        $response = $httpKernel->handle($request)->prepare($request);

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
