<?php

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;

interface HalException
{
    /**
     * @return Hal
     */
    public function getHal();
}
