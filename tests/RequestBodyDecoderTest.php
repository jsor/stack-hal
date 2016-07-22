<?php

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestBodyDecoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideOnKernelRequestData
     */
    public function it_decodes_request_body(
        Request $request,
        $method,
        $expectedParameters,
        $contentType = null,
        array $decoders = null
    ) {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle');

        $app = new RequestBodyDecoder($kernel, $decoders);

        $request->setMethod($method);

        if ($contentType) {
            $request->headers->set('Content-Type', $contentType);
        }

        $app->handle($request);

        $this->assertEquals($request->request->all(), $expectedParameters);
    }

    public static function provideOnKernelRequestData()
    {
        return [
            'Empty POST request' => [new Request([], [], [], [], [], [], ''), 'POST', [], 'application/json'],
            'Empty PUT request' => [new Request([], [], [], [], [], [], ''), 'PUT', [], 'application/json'],
            'Empty PATCH request' => [new Request([], [], [], [], [], [], ''), 'PATCH', [], 'application/json'],
            'Empty DELETE request' => [new Request([], [], [], [], [], [], ''), 'DELETE', [], 'application/json'],
            'Empty GET request' => [new Request([], [], [], [], [], [], ''), 'GET', [], 'application/json'],
            'JSON POST request' => [new Request([], [], [], [], [], [], '["foo"]'), 'POST', ['foo'], 'application/json'],
            'JSON PUT request' => [new Request([], [], [], [], [], [], '["foo"]'), 'PUT', ['foo'], 'application/json'],
            'JSON PATCH request' => [new Request([], [], [], [], [], [], '["foo"]'), 'PATCH', ['foo'], 'application/json'],
            'JSON DELETE request' => [new Request([], [], [], [], [], [], '["foo"]'), 'DELETE', ['foo'], 'application/json'],
            'JSON GET request' => [new Request([], [], [], [], [], [], '["foo"]'), 'GET', [], 'application/json'],
            'POST request with parameters' => [new Request([], ['bar'], [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], '["foo"]'), 'POST', ['bar'], 'application/x-www-form-urlencoded'],
            'POST request with parameters and no Content-Type' => [new Request([], ['bar'], [], [], [], [], ''), 'POST', ['bar']],
            'POST request with unallowed format' => [new Request([], [], [], [], [], [], '["foo"]'), 'POST', [], 'application/fooformat'],
            'POST request with no Content-Type' => [new Request([], [], ['_format' => 'json'], [], [], [], '["foo"]'), 'POST', ['foo']],
            'POST request with invalid decoder' => [new Request([], [], [], [], [], [], '["foo"]'), 'POST', [], 'application/json', ['json' => 'foo']],
        ];
    }

    /**
     * @test
     */
    public function it_returns_bad_request_response_when_decoder_throws()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestBodyDecoder($kernel, ['json' => function () {
            throw new \Exception('Foo');
        }]);

        $request = new Request([], [], [], [], [], [], '["foo"]');
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/json');

        $response = $app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_bad_request_exception_when_decoder_throws_and_catch_is_false()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\BadRequestHttpException');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestBodyDecoder($kernel, ['json' => function () {
            throw new \Exception('Foo');
        }]);

        $request = new Request([], [], [], [], [], [], '["foo"]');
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/json');

        $app->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
    }

    /**
     * @test
     */
    public function it_returns_bad_request_response_when_decoder_returns_non_array()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestBodyDecoder($kernel, ['json' => function () {
            return '';
        }]);

        $request = new Request([], [], [], [], [], [], '["foo"]');
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/json');

        $response = $app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_bad_request_exception_when_decoder_returns_non_array_and_catch_is_false()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\BadRequestHttpException');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->never())
            ->method('handle');

        $app = new RequestBodyDecoder($kernel, ['json' => function () {
            return '';
        }]);

        $request = new Request([], [], [], [], [], [], '["foo"]');
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/json');

        $app->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
    }
}
