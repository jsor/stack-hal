<?php

namespace Jsor\Stack\Hal\Exception;

class ErrorExceptionTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function it_serializes_exception_to_json()
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
    public function it_alllows_errors_to_be_arrays()
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
