<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;

final class CreatedResponseTest extends TestCase
{
    /** @test */
    public function it_sets_location_header(): void
    {
        $hal = new Hal('http://example.com');

        $response = new CreatedResponse($hal);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }
}
