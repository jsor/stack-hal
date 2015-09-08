<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;

class CreatedResponse extends HalResponse
{
    public function __construct(Hal $hal, $headers = [], $prettyPrint = true)
    {
        parent::__construct($hal, 201, $headers, $prettyPrint);

        if (!$this->headers->has('Location') && null !== ($url = $hal->getUri())) {
            $this->headers->set('Location', $url);
        }
    }

    public static function create(
        $hal = null,
        $status = 201,
        $headers = [],
        $prettyPrint = true
    ) {
        return new static($hal, $headers, $prettyPrint);
    }
}
