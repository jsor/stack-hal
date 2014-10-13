<?php

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ErrorException extends BadRequestHttpException implements HalException
{
    private $errors;
    private $logref;

    public function __construct(array $errors,
                                $message = null,
                                $logref = null,
                                \Exception $previous = null,
                                $code = 0)
    {
        parent::__construct($message, $previous, $code);

        $this->errors = $errors;
        $this->logref = $logref;
    }

    public function getHal()
    {
        $data = array(
            'message' => $this->getMessage()
        );

        if ($this->logref) {
            $data['@logref'] = $this->logref;
        }

        $hal = new Hal(null, $data);

        $this->appendErrors($hal, $this->errors);

        return $hal;
    }

    private function appendErrors(Hal $hal, array $errors)
    {
        foreach ($errors as $error) {
            $data = array(
                'message' => (string) $error
            );

            $hal->addResource('errors', new Hal(null, $data));
        }
    }
}