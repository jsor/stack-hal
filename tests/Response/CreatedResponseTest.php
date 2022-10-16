<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;

final class CreatedResponseTest extends TestCase
{
    use HalResponseTestCase;

    protected function provideResponse(Hal $hal = null): HalResponse
    {
        return new CreatedResponse($hal ?: new Hal());
    }

    /** @test */
    public function it_sets_location_header(): void
    {
        $hal = new Hal('http://example.com');

        $response = new CreatedResponse($hal);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }
}
