<?php

namespace Jsor\Stack\Hal\Exception;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ValidatorBuilder;

class ErrorExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_serializes_exception_to_json()
    {
        $errors = [
          'Error'
        ];

        $exception = new ErrorException($errors, 'Error', 100);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Error',
                    'logref' => 100,
                    '_embedded' =>
                        [
                            'errors' =>
                                [
                                    [
                                        'message' => 'Error'
                                    ],
                                ],
                        ],
                ]
            ),
            $exception->getHal()->asJson()
        );
    }
}
