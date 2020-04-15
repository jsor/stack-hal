<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class HalResponseTest extends TestCase
{
    use HalResponseTestCase;

    protected function provideResponse(Hal $hal = null): HalResponse
    {
        return new HalResponse($hal ?: new Hal());
    }

    /** @test */
    public function it_sets_default_content_type_header(): void
    {
        $response = $this->provideResponse();
        $this->assertSame('application/hal+json', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_sets_content_type_header_depending_on_request_format(): void
    {
        $response = $this->provideResponse();

        $request = new Request();
        $request->setRequestFormat('xml');
        $response->prepare($request);
        $this->assertSame('application/hal+xml', $response->headers->get('Content-Type'));
    }
}
