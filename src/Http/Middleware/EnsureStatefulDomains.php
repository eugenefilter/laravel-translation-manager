<?php

namespace Oleinykov\LaravelTranslationManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureStatefulDomains
{
    public function handle(Request $request, Closure $next)
    {
        $configured = config('translation-manager.stateful_domains', '');

        // If empty, allow from any domain
        if ($configured === null || $configured === '') {
            return $next($request);
        }

        $allowed = $this->parseDomains($configured);

        $host = $request->getHost();
        // Strip port if present
        $host = strtolower(preg_replace('/:\\d+$/', '', $host));

        // Allow localhost IPv6 shorthand
        if ($host === '::1') {
            $host = '127.0.0.1';
        }

        foreach ($allowed as $domain) {
            if ($this->matchesHost($host, $domain)) {
                return $next($request);
            }
        }

        abort(403, 'This host is not allowed for Translation Manager');
    }

    private function parseDomains($value): array
    {
        if (is_array($value)) {
            $domains = $value;
        } else {
            $domains = array_filter(array_map('trim', explode(',', (string) $value)));
        }
        return array_map(function ($d) {
            // normalize, strip protocol and port
            $d = preg_replace('#^https?://#', '', strtolower($d));
            $d = preg_replace('/:\\d+$/', '', $d);
            return $d;
        }, $domains);
    }

    private function matchesHost(string $host, string $allowed): bool
    {
        if ($host === $allowed) {
            return true;
        }
        // Support leading wildcard like *.example.com
        if (str_starts_with($allowed, '*.')) {
            $suffix = substr($allowed, 1); // .example.com
            return str_ends_with($host, $suffix);
        }
        return false;
    }
}

