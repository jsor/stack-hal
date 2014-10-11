<?php

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationList;

class ValidationErrorException extends BadRequestHttpException implements HalException
{
    private $violationList;
    private $logref;

    public function __construct(ConstraintViolationList $violationList,
                                $message = null,
                                $logref = null,
                                \Exception $previous = null,
                                $code = 0)
    {
        parent::__construct($message, $previous, $code);

        $this->violationList = $violationList;
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

        foreach ($this->violationList as $violation){
            $path = str_replace('][', '/', $violation->getPropertyPath());
            $path = '/' . trim($path, '[]');

            $data = array(
                'message' => $violation->getMessage(),
                'path' => $path
            );

            $hal->addResource('errors', new Hal(null, $data));
        }

        return $hal;
    }
}
