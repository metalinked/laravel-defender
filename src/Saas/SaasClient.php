<?php

namespace Metalinked\LaravelDefender\Saas;

use Illuminate\Support\Facades\Http;

class SaasClient {
    protected string|null $token;
    protected string $endpoint = 'https://saas.example.com/api/defender/ping'; // FIXME: Update with actual SaaS endpoint

    public function __construct() {
        $this->token = config('defender.saas_token');
    }

    public function ping() {
        if (!$this->token) {
            return [
                'success' => false,
                'message' => 'No SaaS token configured.',
            ];
        }

        $response = Http::withToken($this->token)
            ->get($this->endpoint);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => $response->body(),
        ];
    }
}
