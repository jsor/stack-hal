<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Exception;

use PHPUnit\Framework\TestCase;

final class ErrorExceptionTest extends TestCase
{
    /** @test */
    public function it_serializes_exception_to_json(): void
    {
        $errors = [
          'Error',
        ];

        $exception = new ErrorException($errors, 'Error', 100);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            \json_encode(
                [
                    'message' => 'Error',
                    'logref' => 100,
                    '_embedded' => [
                            'errors' => [
                                    [
                                        'message' => 'Error',
                                    ],
                                ],
                        ],
                ]
            ),
            $exception->getHal()->asJson()
        );
    }

    /** @test */
    public function it_alllows_errors_to_be_arrays(): void
    {
        $errors = [
            [
                'message' => 'Error',
                'path' => '/foo',
            ],
        ];

        $exception = new ErrorException($errors, 'Error', 100);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            \json_encode(
                [
                    'message' => 'Error',
                    'logref' => 100,
                    '_embedded' => [
                            'errors' => [
                                    [
                                        'message' => 'Error',
                                        'path' => '/foo',
                                    ],
                                ],
                        ],
                ]
            ),
            $exception->getHal()->asJson()
        );
    }
}
