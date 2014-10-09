<?php

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\RequestFormatValidator;
use Jsor\Stack\Hal\ResponseConverter;
use Nocarrier\Hal;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @group no-deps
 */
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

        $app = new ResponseConverter($httpKernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = Request::create('/');
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/hal+xml, application/xml.', $response->getContent());
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

        $app = new ResponseConverter($httpKernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = Request::create('/');
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

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

        $app = new ResponseConverter($httpKernel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = Request::create('/exception');
        $request->attributes->set('_format', 'json');

        $response = $app->handle($request);

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
