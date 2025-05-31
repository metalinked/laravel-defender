<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use LaravelDefender\Http\Middleware\HoneypotMiddleware;
use PHPUnit\Framework\TestCase;

class HoneypotMiddlewareTest extends TestCase
{
    public function test_blocks_request_with_filled_honeypot_field()
    {
        $middleware = new HoneypotMiddleware();

        $request = Request::create('/test', 'POST', [
            config('defender.honeypot.field_prefix', 'my_full_name_') . 'abc' => 'bot',
            'valid_from' => encrypt(now()->timestamp - 10),
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $middleware->handle($request, fn() => null);
    }

    public function it_blocks_request_submitted_too_quickly()
    {
        $prefix = config('defender.honeypot.field_prefix', 'my_full_name_');
        $field = $prefix . 'xyz';

        Route::post('/test', function () {
            return 'ok';
        })->middleware('defender.honeypot');

        $response = $this->post('/test', [
            $field => '',
            'valid_from' => Crypt::encrypt(now()->timestamp),
        ]);

        $response->assertStatus(422);
    }
}