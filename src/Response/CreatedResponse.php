<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

use InvalidArgumentException;
use Nocarrier\Hal;

final class CreatedResponse extends HalResponse
{
    public function __construct(Hal $hal, array $headers = [], bool $prettyPrint = true)
    {
        parent::__construct($hal, 201, $headers, $prettyPrint);

        if (!$this->headers->has('Location') && null !== ($url = $hal->getUri())) {
            $this->headers->set('Location', $url);
        }
    }

    /**
     * @return $this
     */
    public function setContent(?string $content): static
    {
        if (null !== $content) {
            throw new InvalidArgumentException('Cannot set content to a string. Use CreatedResponse::setHal() to set a Hal instance.');
        }

        $this->hal = $content;

        return $this;
    }
}
