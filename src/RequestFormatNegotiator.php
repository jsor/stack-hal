<?php

namespace Jsor\Stack\Hal;

use Jsor\Stack\Hal\Negotiation\HalNegotiator;
use Negotiation\Decoder\DecoderProviderInterface;
use Negotiation\FormatNegotiatorInterface;
use Negotiation\NegotiatorInterface;
use Negotiation\Stack\Negotiation;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestFormatNegotiator extends Negotiation
{
    public function __construct(
        HttpKernelInterface $app,
        FormatNegotiatorInterface $formatNegotiator = null,
        NegotiatorInterface $languageNegotiator = null,
        DecoderProviderInterface $decoderProvider = null
    ) {
        if (!$formatNegotiator) {
            $formatNegotiator = new HalNegotiator();
        }

        parent::__construct($app, $formatNegotiator, $languageNegotiator, $decoderProvider);
    }
}
