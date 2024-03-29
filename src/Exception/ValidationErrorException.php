<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

final class ValidationErrorException extends BadRequestHttpException implements HalException
{
    private ConstraintViolationListInterface $violationList;
    private mixed $logref;

    public function __construct(
        ConstraintViolationListInterface $violationList,
        string $message = '',
        mixed $logref = null,
        Throwable $previous = null,
        int $code = 0,
    ) {
        parent::__construct($message, $previous, $code);

        $this->violationList = $violationList;
        $this->logref = $logref;
    }

    public function getHal(): Hal
    {
        $data = [
            'message' => $this->getMessage(),
        ];

        if ($this->logref) {
            $data['@logref'] = $this->logref;
        }

        $hal = new Hal(null, $data);

        foreach ($this->violationList as $violation) {
            $path = str_replace('][', '/', $violation->getPropertyPath());
            $path = '/' . trim($path, '[]');

            $data = [
                'message' => $violation->getMessage(),
                'path' => $path,
            ];

            $hal->addResource('errors', new Hal(null, $data));
        }

        return $hal;
    }
}
