<?php


namespace Rackbeat\Utils;


use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;

class RateLimiterStore implements Store
{
    public ?string $rateLimitingToken = null;

    public function __construct($rateLimitingToken) {
        $this->rateLimitingToken = (string) $rateLimitingToken;
    }

    
    public function get(): array
    {
        return Cache::get('rate-limiter-' . $this->rateLimitingToken, []);
    }

    public function push(int $timestamp)
    {
        Cache::put('rate-limiter-' . $this->rateLimitingToken, array_merge($this->get(), [$timestamp]), Carbon::now()->addMinutes(5));
    }
}
