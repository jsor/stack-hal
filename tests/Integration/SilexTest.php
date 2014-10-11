<?php

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\RequestFormatValidator;
use Jsor\Stack\Hal\ResponseConverter;
use Nocarrier\Hal;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group deps-silex
 */
class SilexTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_intercepts_not_acceptable_format()
    {
        $silex = new Application();
        $silex['debug'] = true;

        $app = new ResponseConverter($silex);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = Request::create('/');
        $request->attributes->set('_format', 'html');

        $response = $app->handle($request)->prepare($request);

        $this->assertSame(406, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Format "html" is not supported. Supported mime types are: application/hal+json, application/json, application/hal+xml, application/xml.', $response->getContent());
    }

    /** @test */
    public function it_converts_response_to_json()
    {
        $silex = new Application();
        $silex['debug'] = true;
        $silex->get('/', function () {
            return new Hal('/');
        });

        $app = new ResponseConverter($silex);
        $app = new RequestFormatValidator($app);
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
        $silex = new Application();
        $silex['debug'] = true;
        $silex->get('/', function () {
            throw new NotFoundHttpException();
        });

        $app = new ResponseConverter($silex);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = Request::create('/');
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
