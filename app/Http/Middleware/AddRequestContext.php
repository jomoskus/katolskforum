<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tags every request with a request id and the acting user, so log lines,
 * queued jobs and error reports can be correlated. The id is echoed back
 * as an X-Request-Id header that users can quote when reporting problems.
 */
class AddRequestContext
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->requestId($request);

        Context::add([
            'request_id' => $requestId,
            'user_id' => $request->user()?->id,
        ]);

        $response = $next($request);

        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }

    /**
     * Reuse an incoming X-Request-Id (from a proxy or retrying client) when
     * it is a well-formed UUID; otherwise generate a fresh one. The format
     * check keeps attacker-controlled junk out of the logs.
     */
    private function requestId(Request $request): string
    {
        $incoming = $request->header('X-Request-Id');

        if (is_string($incoming) && Str::isUuid($incoming)) {
            return $incoming;
        }

        return (string) Str::uuid();
    }
}
