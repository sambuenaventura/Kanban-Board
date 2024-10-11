<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Closure;

trait IdempotentRequest
{
    protected function processIdempotentRequest(Request $request, string $operationKey, Closure $operation)
    {
        $idempotencyKey = $request->input('idempotency_key');

        if (empty($idempotencyKey)) {
            return $this->errorResponse('Idempotency key is required.');
        }

        $cacheKey = "{$operationKey}_{$idempotencyKey}";

        if (Cache::has($cacheKey)) {
            return $this->alreadyProcessedResponse($operationKey);
        }

        $result = $operation();

        Cache::put($cacheKey, true, now()->addDay());

        return $result;
    }

    protected function errorResponse($message)
    {
        return redirect()->back()->with('error', $message);
    }

    protected function alreadyProcessedResponse($operationKey)
    {
        $action = ucfirst(str_replace('_', ' ', $operationKey));
        return redirect()->back()->with('warning', "This action has already been processed.");
    }
}