<?php

namespace Jsor\Stack\Hal;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestFormatNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideAcceptHeaders
     */
    public function it_accepts_hal_headers($acceptHeader, $format)
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle');

        $app = new RequestFormatNegotiator($kernel);

        $request = new Request();
        $request->headers->set('Accept', $acceptHeader);

        $app->handle($request);

        $this->assertEquals($format, $request->getRequestFormat(null));
        $this->assertEquals($format, $request->attributes->get('_format'));
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
            array('text/html; q=0.0', 'html'),
        );
    }
}
