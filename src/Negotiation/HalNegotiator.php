<?php

namespace Jsor\Stack\Hal\Negotiation;

use Negotiation\FormatNegotiator;

class HalNegotiator extends FormatNegotiator
{
    protected $formats = array(
        'json' => ['application/hal+json', 'application/json', 'application/x-json'],
        'xml' => ['application/hal+xml', 'text/xml', 'application/xml', 'application/x-xml']
    );
}
