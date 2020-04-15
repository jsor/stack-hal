<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;

final class CreatedResponse extends HalResponse
{
    public function __construct(Hal $hal, $headers = [], $prettyPrint = true)
    {
        parent::__construct($hal, 201, $headers, $prettyPrint);

        if (!$this->headers->has('Location') && null !== ($url = $hal->getUri())) {
            $this->headers->set('Location', $url);
        }
    }
}
