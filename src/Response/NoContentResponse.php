<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Response;

class NoContentResponse extends Response
{
    protected $location;

    public function __construct($location = null, $headers = array())
    {
        parent::__construct(null, 204, $headers);

        if (!empty($location)) {
            $this->headers->set('Location', $location);
        }
    }

    public static function create($location = null, $status = 204, $headers = array())
    {
        return new static($location, $headers);
    }

    public static function convert($value)
    {
        if ($value instanceof Hal) {
            return new static($value->getUri());
        }

        if ($value instanceof Response) {
            $uri = $value instanceof HalResponse ? $value->getHal()->getUri() : null;

            return new static($uri, $value->headers->all());
        }

        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            return new static($value);
        }

        return new static();
    }

    public static function convertIfSuccessful($value)
    {
        if ($value instanceof Response && !$value->isSuccessful()) {
            return $value;
        }

        return static::convert($value);
    }
}
