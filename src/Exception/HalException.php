<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;

interface HalException
{
    public function getHal(): Hal;
}
