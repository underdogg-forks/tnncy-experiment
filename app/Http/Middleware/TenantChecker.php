<?php

namespace App\Http\Middleware;

use Closure;
use Hyn\Tenancy\Environment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hostname = app(Environment::class)->hostname();
        $fqdn = $hostname?->fqdn;

        if (! $fqdn) {
            return response()->json(['error' => 'No Tenant Implemented'], 501);
        }

        config(['database.default' => 'tenant']);

        return $next($request);
    }
}
