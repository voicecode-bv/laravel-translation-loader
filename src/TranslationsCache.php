<?php

namespace Esign\TranslationLoader;

use Closure;
use DateInterval;
use Illuminate\Cache\Repository;

class TranslationsCache
{
    public function __construct(
        protected Repository $store,
        protected string $key,
        protected DateInterval $ttl,
    ) {
    }

    public function remember(Closure $callback): mixed
    {
        return $this
            ->store
            ->remember($this->key, $this->ttl, $callback);
    }

    public function rememberForGroup(string $groupKey, Closure $callback): mixed
    {
        $fullKey = $this->key . '.' . $groupKey;
        
        return $this
            ->store
            ->remember($fullKey, $this->ttl, $callback);
    }

    public function forget(): bool
    {
        return $this
            ->store
            ->forget($this->key);
    }

    public function forgetGroup(string $groupKey): bool
    {
        $fullKey = $this->key . '.' . $groupKey;
        
        return $this
            ->store
            ->forget($fullKey);
    }

    public function flush(): bool
    {
        return $this
            ->store
            ->flush();
    }
}
