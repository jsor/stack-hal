<?php

namespace Jsor\Stack\Hal\Response;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HalResponse extends Response
{
    protected $hal;
    protected $requestFormat;
    protected $prettyPrint;

    public function __construct(Hal $hal, $status = 200, $headers = [], $prettyPrint = true)
    {
        parent::__construct(null, $status, $headers);

        $this->hal = $hal;
        $this->prettyPrint = (bool) $prettyPrint;

        $this->requestFormat = 'json';
        $this->headers->set('Content-Type', 'application/hal+json');
    }

    public static function create($hal = null, $status = 200, $headers = [], $prettyPrint = true)
    {
        return new static($hal, $status, $headers, $prettyPrint);
    }

    public function prepare(Request $request)
    {
        if ('xml' === $request->getRequestFormat()) {
            $this->requestFormat = 'xml';
            $this->headers->set('Content-Type', 'application/hal+xml');
        }

        return parent::prepare($request);
    }

    public function sendContent()
    {
        echo $this->getContent();

        return $this;
    }

    public function setContent($content)
    {
        if (null !== $content && !$content instanceof Hal) {
            throw new \LogicException('The content must be a Hal instance.');
        }

        $this->hal = $content;
    }

    public function getContent()
    {
        if (null === $this->hal) {
            return '';
        }

        switch ($this->requestFormat) {
            case 'xml':
                return $this->hal->asXml($this->prettyPrint);
            default:
                return $this->hal->asJson($this->prettyPrint);
        }
    }

    public function getHal()
    {
        return $this->hal;
    }
}
