<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::connection()->getPdo()),
            'cache' => $this->check(fn () => Cache::store()->put('health', true, 10)),
        ];

        $healthy = ! in_array(false, array_column($checks, 'ok'), true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'release' => config('app.release'),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /**
     * @return array{ok: bool, ms: float, error?: string}
     */
    private function check(callable $probe): array
    {
        $start = hrtime(true);

        try {
            $probe();

            return ['ok' => true, 'ms' => $this->elapsed($start)];
        } catch (Throwable $e) {
            report($e);

            return ['ok' => false, 'ms' => $this->elapsed($start), 'error' => $e->getMessage()];
        }
    }

    private function elapsed(float $start): float
    {
        return round((hrtime(true) - $start) / 1_000_000, 2);
    }
}
