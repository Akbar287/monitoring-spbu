<?php

namespace Tests\Unit;

use App\Http\Middleware\AddCacheControlHeaders;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class AddCacheControlHeadersTest extends TestCase
{
    public function test_request_is_executed_once_and_original_response_is_returned(): void
    {
        $calls = 0;
        $expected = new \Illuminate\Http\Response('script', 200, ['Content-Type' => 'application/javascript']);
        $response = (new AddCacheControlHeaders)->handle(Request::create('/test', 'POST'), function () use (&$calls, $expected): Response {
            $calls++;

            return $expected;
        });

        $this->assertSame(1, $calls);
        $this->assertSame($expected, $response);
        $this->assertStringContainsString('max-age=604800', $response->headers->get('Cache-Control'));
    }
}
