<?php

namespace Jsor\Stack\Hal\Integration;

use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\RequestFormatValidator;
use Jsor\Stack\Hal\ResponseConverter;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group deps-laravel
 */
class LaravelTest extends \PHPUnit_Framework_TestCase
{
    protected $laravel;

    public function setUp()
    {
        $paths = file_get_contents(__DIR__.'/../../vendor/laravel/laravel/bootstrap/paths.php');
        $paths = str_replace("'base' => __DIR__.'/..',", "'base' => '".__DIR__."/../..',", $paths);
        file_put_contents(__DIR__.'/../../vendor/laravel/laravel/bootstrap/paths.php', $paths);

        $unitTesting = true;
        $testEnvironment = 'testing';

        $this->laravel = require __DIR__.'/../../vendor/laravel/laravel/bootstrap/start.php';

        $this->laravel['router']->get('/response', function () {
            return new Hal('/');
        });
        $this->laravel['router']->get('/exception', function () {
            throw new NotFoundHttpException();
        });

        // Filters are disabled by the RoutingServiceProvider for env 'testing'
        $this->laravel['router']->enableFilters();
    }

    /** @test */
    public function it_intercepts_not_acceptable_format()
    {
        $app = new ResponseConverter($this->laravel);
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
        $app = new ResponseConverter($this->laravel);
        $app = new RequestFormatValidator($app);
        $app = new ExceptionConverter($app);

        $request = Request::create('/response');
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
        $this->markTestSkipped('Requires https://github.com/laravel/framework/pull/6027 to be merged');

        $this->laravel['env'] = 'not_testing';

        $app = new ResponseConverter($this->laravel);
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
