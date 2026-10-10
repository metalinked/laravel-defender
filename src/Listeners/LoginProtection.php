<?php

namespace Metalinked\LaravelDefender\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Metalinked\LaravelDefender\Defender;

/**
 * Counts failed logins per IP through Laravel's auth events, so it works with
 * any login that goes through Auth::attempt(). Once an IP reaches the limit,
 * further attempts are refused *before* the credentials are checked: a correct
 * password guessed after the limit gets in no more than a wrong one.
 */
class LoginProtection {
    public function failed(Failed $event): void {
        $request = $this->request();

        if (! $request || ! config('defender.login_protection.enabled', true) || Defender::isWhitelisted($request)) {
            return;
        }

        $key = $this->key($request);
        Cache::add($key, 0, now()->addMinutes((int) config('defender.login_protection.decay_minutes', 15)));
        Cache::increment($key);
    }

    public function attempting(Attempting $event): void {
        $request = $this->request();

        if (! $request || ! config('defender.login_protection.enabled', true) || Defender::isWhitelisted($request)) {
            return;
        }

        $max = (int) config('defender.login_protection.max_attempts', 5);

        if ((int) Cache::get($this->key($request), 0) < $max) {
            return;
        }

        $response = Defender::block($request, __('defender::defender.alert_too_many_login_attempts'), 429);

        if ($response) {
            throw new HttpResponseException($response);
        }
    }

    protected function request(): ?Request {
        return app()->bound('request') ? app('request') : null;
    }

    protected function key(Request $request): string {
        return 'defender:login_failures:' . $request->ip();
    }
}
