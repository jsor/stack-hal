<?php

namespace Jsor\Stack\Hal\Negotiation;

class HalNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideAcceptHeaders
     */
    public function it_accepts_hal_headers($acceptHeader, $expected)
    {
        $negotiator = new HalNegotiator();

        $this->assertEquals($expected, $negotiator->getBestFormat($acceptHeader));
    }

    public static function provideAcceptHeaders()
    {
        return [
            ['application/hal+json,application/json;q=0.9,*/*;q=0.8', 'json'],
            ['application/json;q=0.9,*/*;q=0.8', 'json'],
            ['application/x-json;q=0.9,*/*;q=0.8', 'json'],

            ['application/hal+xml,text/xml;q=0.9,*/*;q=0.8', 'xml'],
            ['text/xml;q=0.9,*/*;q=0.8', 'xml'],
            ['application/xml;q=0.9,*/*;q=0.8', 'xml'],
            ['application/x-xml;q=0.9,*/*;q=0.8', 'xml'],

            ['text/html, application/json;q=0.8, text/csv;q=0.7', 'html'],
            ['text/html', 'html'],
            ['text/*, text/html, text/html;level=1, */*', 'html'],
            ['text/html; q=0.0', null],
        ];
    }
}
