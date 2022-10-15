<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

use LogicException;
use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HalResponse extends Response
{
    protected $hal;
    protected $requestFormat;
    protected $prettyPrint;

    public function __construct(
        Hal $hal,
        int $status = 200,
        array $headers = [],
        bool $prettyPrint = true,
    ) {
        parent::__construct(null, $status, $headers);

        $this->hal = $hal;
        $this->prettyPrint = $prettyPrint;

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

    public function setContent($content): static
    {
        if (null !== $content && !$content instanceof Hal) {
            throw new LogicException('The content must be a Hal instance.');
        }

        $this->hal = $content;

        return $this;
    }

    public function getContent(): string
    {
        if (null === $this->hal) {
            return '';
        }

        if ('xml' === $this->requestFormat) {
            return $this->hal->asXml($this->prettyPrint);
        }

        return $this->hal->asJson($this->prettyPrint);
    }

    public function getHal(): ?Hal
    {
        return $this->hal;
    }
}
