<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SiteCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('> site', $request->toArray());

        if ($request->action !== 'subscribe-course' &&
            $request->action !== 'subscribe-school' &&
            $request->action !== 'order' &&
            $request->action !== 'order-installment' &&
            $request->action !== 'subscribe-school-api') {

            return $next($request);
        }
        exit;
    }
}
