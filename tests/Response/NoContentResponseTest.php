<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;

class NoContentResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_sets_location_header()
    {
        $response = NoContentResponse::create('http://example.com');
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }

    /** @test */
    public function it_converts_hal_instance()
    {
        $hal = new Hal('http://example.com');

        $response = NoContentResponse::convert($hal);
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }

    /** @test */
    public function it_converts_hal_response_instance()
    {
        $halResponse = new HalResponse(new Hal('http://example.com'));

        $response = NoContentResponse::convert($halResponse);
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }

    /** @test */
    public function it_converts_string_url()
    {
        $url = 'http://example.com';

        $response = NoContentResponse::convert($url);
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }

    /** @test */
    public function it_ignores_invalid_string_url()
    {
        $url = 'foo';

        $response = NoContentResponse::convert($url);
        $this->assertNull($response->headers->get('Location'));
    }

    /** @test */
    public function it_converts_response_if_successful()
    {
        $halResponse = new HalResponse(new Hal('http://example.com'));

        $response = NoContentResponse::convertIfSuccessful($halResponse);
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }

    /** @test */
    public function it_ignores_response_if_not_successful()
    {
        $errorResponse = new VndErrorResponse(new Hal('http://example.com'));

        $response = NoContentResponse::convertIfSuccessful($errorResponse);
        $this->assertNull($response->headers->get('Location'));
    }
}
