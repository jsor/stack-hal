<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;

class CreatedResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_sets_location_header()
    {
        $hal = new Hal('http://example.com');

        $response = CreatedResponse::create($hal);
        $this->assertSame('http://example.com', $response->headers->get('Location'));
    }
}
