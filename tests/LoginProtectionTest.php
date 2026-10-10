<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class LoginProtectionTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Http::fake();
        config(['auth.providers.users.model' => LoginTestUser::class]);

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('email');
            $table->string('password');
            $table->rememberToken();
        });
        LoginTestUser::create(['email' => 'ana@example.com', 'password' => Hash::make('right-password')]);

        Route::middleware('web')->post('/login', function (Request $request) {
            return Auth::attempt($request->only('email', 'password'))
                ? response('welcome')
                : response('bad credentials', 422);
        });
    }

    private function login(string $password) {
        return $this->post('/login', ['email' => 'ana@example.com', 'password' => $password]);
    }

    public function test_blocks_after_max_failed_attempts(): void {
        config(['defender.login_protection.max_attempts' => 3]);

        for ($i = 0; $i < 3; $i++) {
            $this->login("wrong-$i")->assertStatus(422);
        }

        $this->login('wrong-again')->assertStatus(429);
        $this->assertDatabaseHas('defender_ip_logs', ['reason' => 'Too many failed login attempts from this IP']);
    }

    public function test_correct_password_is_refused_once_blocked(): void {
        config(['defender.login_protection.max_attempts' => 2]);

        $this->login('wrong-1');
        $this->login('wrong-2');

        $this->login('right-password')->assertStatus(429);
        $this->assertGuest();
    }

    public function test_successful_login_below_limit_works(): void {
        $this->login('wrong-1');

        $this->login('right-password')->assertOk();
    }

    public function test_monitor_mode_logs_without_blocking(): void {
        config(['defender.login_protection.max_attempts' => 1, 'defender.mode' => 'monitor']);

        $this->login('wrong-1');

        $this->login('right-password')->assertOk();
        $this->assertDatabaseHas('defender_ip_logs', ['reason' => 'Too many failed login attempts from this IP']);
    }

    public function test_can_be_disabled(): void {
        config(['defender.login_protection.max_attempts' => 1, 'defender.login_protection.enabled' => false]);

        $this->login('wrong-1');

        $this->login('right-password')->assertOk();
    }

    public function test_whitelisted_ip_is_never_throttled(): void {
        config(['defender.login_protection.max_attempts' => 1, 'defender.whitelist.ips' => ['127.0.0.1']]);

        $this->login('wrong-1');

        $this->login('right-password')->assertOk();
    }
}

class LoginTestUser extends User {
    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = false;
}
