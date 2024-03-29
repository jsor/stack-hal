<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Response;

use InvalidArgumentException;
use Nocarrier\Hal;

trait HalResponseTestCase
{
    /** @test */
    public function it_allows_setting_hal_instance(): void
    {
        $response = $this->provideResponse();

        $response->setHal(new Hal(null, ['message' => 'test']));

        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'test',
                ],
            ),
            $response->getContent(),
        );
    }

    /** @test */
    public function it_allows_setting_null_content(): void
    {
        $response = $this->provideResponse();

        $response->setContent(null);

        $this->assertFalse($response->getContent());
    }

    /** @test */
    public function it_throws_exception_for_non_hal_content(): void
    {
        $response = $this->provideResponse();

        $this->expectException(InvalidArgumentException::class);

        $response->setContent('');
    }

    /** @test */
    public function is_sends_hal_content(): void
    {
        $response = $this->provideResponse(new Hal(null, ['message' => 'test']));

        ob_start();
        $response->sendContent();
        $string = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'test',
                ],
            ),
            $string,
        );
    }

    /** @test */
    public function it_returns_hal_instance(): void
    {
        $hal = new Hal(null, ['message' => 'test']);

        $response = $this->provideResponse($hal);

        $this->assertSame($hal, $response->getHal());
    }

    abstract protected function provideResponse(Hal $hal = null): HalResponse;
}
