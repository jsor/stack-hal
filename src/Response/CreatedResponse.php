<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;

class CreatedResponse extends HalResponse
{
    public function __construct(Hal $hal, $headers = array(), $prettyPrint = false)
    {
        parent::__construct($hal, 204, $headers, $prettyPrint);

        if (!$this->headers->has('Location') && null !== ($url = $hal->getUri())) {
            $this->headers->set('Location', $url);
        }
    }

    public static function create($hal = null, $status = 204, $headers = array(), $prettyPrint = false)
    {
        return new static($hal, $headers, $prettyPrint);
    }
}
