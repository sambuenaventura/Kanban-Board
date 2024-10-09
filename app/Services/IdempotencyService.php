<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class IdempotencyService
{
    public function process(string $operationKey, string $idempotencyKey, \Closure $operation)
    {
        $cacheKey = "{$operationKey}_{$idempotencyKey}";

        if (Cache::has($cacheKey)) {
            return [
                'status' => 'warning',
                'message' => 'This operation has already been processed.',
            ];
        }

        $result = $operation();

        Cache::put($cacheKey, true, now()->addDay());

        return $result;
    }
}
