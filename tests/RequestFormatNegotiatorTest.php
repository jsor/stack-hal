<?php

namespace Jsor\Stack\Hal;

use Symfony\Component\HttpFoundation\Request;

class RequestFormatNegotiatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @dataProvider provideAcceptHeaders
     */
    public function it_accepts_hal_headers($acceptHeader, $type, $format)
    {
        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $kernel
            ->expects($this->once())
            ->method('handle');

        $app = new RequestFormatNegotiator($kernel);

        $request = new Request();
        $request->headers->set('Accept', $acceptHeader);

        $app->handle($request);

        $this->assertEquals($type, $request->attributes->get('_mime_type'), '_mime_type');

        $this->assertEquals($format, $request->getRequestFormat(null), 'getRequestFormat');
    }

    public static function provideAcceptHeaders()
    {
        return [
            ['application/hal+json,application/json;q=0.9,*/*;q=0.8', 'application/hal+json', 'json'],
            ['application/json;q=0.9,*/*;q=0.8', 'application/json', 'json'],
            ['application/x-json;q=0.9,*/*;q=0.8', 'application/x-json', 'json'],

            ['application/hal+xml,text/xml;q=0.9,*/*;q=0.8', 'application/hal+xml', 'xml'],
            ['text/xml;q=0.9,*/*;q=0.8', 'text/xml', 'xml'],
            ['application/xml;q=0.9,*/*;q=0.8', 'application/xml', 'xml'],
            ['application/x-xml;q=0.9,*/*;q=0.8', 'application/x-xml', 'xml'],

            ['text/html, application/json;q=0.8, text/csv;q=0.7', 'application/json', 'json'],
            ['text/html', null, null],
            ['text/*, text/html, text/html;level=1, */*', 'application/hal+json', 'json'],
            ['text/html; q=0.0', null, null],
            [null, null, null],
        ];
    }
}
