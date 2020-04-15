<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

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
        bool $prettyPrint = true
    ) {
        parent::__construct(null, $status, $headers);

        $this->hal = $hal;
        $this->prettyPrint = $prettyPrint;

        $this->requestFormat = 'json';
        $this->headers->set('Content-Type', 'application/hal+json');
    }

    public function prepare(Request $request): Response
    {
        if ('xml' === $request->getRequestFormat()) {
            $this->requestFormat = 'xml';
            $this->headers->set('Content-Type', 'application/hal+xml');
        }

        return parent::prepare($request);
    }

    public function sendContent(): self
    {
        echo $this->getContent();

        return $this;
    }

    public function setContent($content): void
    {
        if (null !== $content && !$content instanceof Hal) {
            throw new \LogicException('The content must be a Hal instance.');
        }

        $this->hal = $content;
    }

    public function getContent(): string
    {
        if (null === $this->hal) {
            return '';
        }

        if ($this->requestFormat === 'xml') {
            return $this->hal->asXml($this->prettyPrint);
        }

        return $this->hal->asJson($this->prettyPrint);
    }

    public function getHal(): ?Hal
    {
        return $this->hal;
    }
}
