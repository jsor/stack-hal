<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

use InvalidArgumentException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HalResponse extends Response
{
    protected ?Hal $hal;
    protected string $requestFormat;
    protected bool $prettyPrint;

    public function __construct(
        Hal $hal,
        int $status = 200,
        array $headers = [],
        bool $prettyPrint = true,
    ) {
        parent::__construct(null, $status, $headers);

        $this->content = '';
        $this->hal = $hal;
        $this->prettyPrint = $prettyPrint;

        $this->charset = 'UTF-8';
        $this->requestFormat = 'json';
        $this->headers->set('Content-Type', 'application/hal+json');
    }

    public function prepare(Request $request): static
    {
        if ('xml' === $request->getRequestFormat()) {
            $this->requestFormat = 'xml';
            $this->headers->set('Content-Type', 'application/hal+xml');
        }

        return parent::prepare($request);
    }

    public function sendContent(): static
    {
        echo $this->getContent();

        return $this;
    }

    /**
     * @return $this
     */
    public function setContent(?string $content): static
    {
        if (null !== $content) {
            throw new InvalidArgumentException('Cannot set content to a string. Use HalResponse::setHal() to set a Hal instance.');
        }

        $this->hal = $content;

        return $this;
    }

    public function getContent(): string|false
    {
        if (null === $this->hal) {
            return false;
        }

        if ('xml' === $this->requestFormat) {
            return $this->hal->asXml($this->prettyPrint);
        }

        /** @var string $content */
        $content = $this->hal->asJson($this->prettyPrint);

        return $content;
    }

    public function getHal(): ?Hal
    {
        return $this->hal;
    }

    public function setHal(Hal $hal): void
    {
        $this->hal = $hal;
    }
}
