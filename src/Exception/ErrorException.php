<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

use function is_array;

final class ErrorException extends BadRequestHttpException implements HalException
{
    private array $errors;
    private mixed $logref;

    public function __construct(
        array $errors,
        string $message = '',
        mixed $logref = null,
        Throwable $previous = null,
        int $code = 0,
    ) {
        parent::__construct($message, $previous, $code);

        $this->errors = $errors;
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

        $this->appendErrors($hal, $this->errors);

        return $hal;
    }

    private function appendErrors(Hal $hal, array $errors): void
    {
        foreach ($errors as $error) {
            if (!is_array($error)) {
                $error = [
                    'message' => (string) $error,
                ];
            }

            $hal->addResource('errors', new Hal(null, $error));
        }
    }
}
