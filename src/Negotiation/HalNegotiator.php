<?php

namespace Jsor\Stack\Hal\Negotiation;

use Negotiation\FormatNegotiator;

class HalNegotiator extends FormatNegotiator
{
    public function __construct()
    {
        $this->registerFormat(
            'json',
            ['application/hal+json', 'application/json', 'application/x-json'],
            true
        );

        $this->registerFormat(
            'xml',
            ['application/hal+xml', 'text/xml', 'application/xml', 'application/x-xml'],
            true
        );
    }
}
