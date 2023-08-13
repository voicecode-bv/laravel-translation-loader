<?php

namespace Esign\TranslationLoader\Models;

use Esign\TranslationLoader\TranslationsCache;
use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use UnderscoreTranslatable;

    protected $guarded = [];
    protected $table = 'translations';
    public $translatable = [
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => app(TranslationsCache::class)->forget());
        static::deleted(fn () => app(TranslationsCache::class)->forget());
    }
}
