<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;

class HalResponseTest extends \PHPUnit_Framework_TestCase
{
    use HalResponseTestCase;

    protected function provideResponse(Hal $hal = null)
    {
        return HalResponse::create($hal ?: new Hal());
    }

    /** @test */
    public function it_sets_default_content_type_header()
    {
        $response = $this->provideResponse();
        $this->assertSame('application/hal+json', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_sets_content_type_header_depending_on_request_format()
    {
        $response = $this->provideResponse();

        $request = new Request();
        $request->setRequestFormat('xml');
        $response->prepare($request);
        $this->assertSame('application/hal+xml', $response->headers->get('Content-Type'));
    }
}
