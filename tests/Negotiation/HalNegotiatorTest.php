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
        return array(
            array('application/hal+json,application/json;q=0.9,*/*;q=0.8', 'json'),
            array('application/json;q=0.9,*/*;q=0.8', 'json'),
            array('application/x-json;q=0.9,*/*;q=0.8', 'json'),

            array('application/hal+xml,text/xml;q=0.9,*/*;q=0.8', 'xml'),
            array('text/xml;q=0.9,*/*;q=0.8', 'xml'),
            array('application/xml;q=0.9,*/*;q=0.8', 'xml'),
            array('application/x-xml;q=0.9,*/*;q=0.8', 'xml'),

            array('text/html, application/json;q=0.8, text/csv;q=0.7', 'html'),
            array('text/html', 'html'),
            array('text/*, text/html, text/html;level=1, */*', 'html'),
            array('text/html; q=0.0', null),
        );
    }
}
