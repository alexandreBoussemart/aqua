<?php

namespace App\Tests\Unit;

use App\Gpio\GpioClient;
use App\Gpio\GpioException;
use PHPUnit\Framework\TestCase;

final class GpioClientTest extends TestCase
{
    public function testReadStableReturnsNullWhenLevelMoves(): void
    {
        $client = new class('test:0') extends GpioClient {
            /** @var list<array<string, int>> */
            public array $responses = [];

            protected function request(array $payload): array
            {
                return ['ok' => true, 'values' => array_shift($this->responses)];
            }
        };
        $client->responses = [['a' => 1], ['a' => 1], ['a' => 1], ['a' => 0]];
        self::assertSame(['a' => 1], $client->readStable(['a'], 0));
        self::assertNull($client->readStable(['a'], 0));
    }

    public function testUnreachableServiceThrows(): void
    {
        $client = new GpioClient('127.0.0.1:1', 0.5);
        self::assertFalse($client->ping());
        $this->expectException(GpioException::class);
        $client->relays();
    }
}
