<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Http\Middleware\HoneypotMiddleware;

class HoneypotMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    }

    public function test_blocks_request_with_filled_honeypot_field()
    {
        $middleware = new HoneypotMiddleware();

        // Create request with filled honeypot field (bot)
        $request = Request::create('/test', 'POST', [
            config('defender.honeypot.field_prefix') . 'abc' => 'bot',
            'valid_from' => Crypt::encrypt(now()->timestamp - 10),
        ]);

        // Expect middleware to abort with HTTP exception
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, fn () => null);
    }

    public function test_blocks_request_submitted_too_quickly()
    {
        // Define route with middleware to make a real HTTP request
        Route::post('/test', function () {
            return 'ok';
        })->middleware(HoneypotMiddleware::class);

        $prefix = config('defender.honeypot.field_prefix');
        $field = $prefix . 'xyz';

        // Send request with empty honeypot but valid_from too recent (0 seconds)
        $response = $this->post('/test', [
            $field => '',
            'valid_from' => Crypt::encrypt(now()->timestamp),
        ]);

        $response->assertStatus(422);
    }
}
