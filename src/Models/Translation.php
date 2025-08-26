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
        static::saved(function (Translation $translation) {
            $cache = app(TranslationsCache::class);
            $cache->forgetGroup("translations.{$translation->group}.*");
            $cache->forgetGroup("translations.{$translation->group}.en");
            $cache->forgetGroup("translations.{$translation->group}.nl");
            $cache->forget(); // Also clear the legacy full cache for backward compatibility
        });

        static::deleted(function (Translation $translation) {
            $cache = app(TranslationsCache::class);
            $cache->forgetGroup("translations.{$translation->group}.*");
            $cache->forgetGroup("translations.{$translation->group}.en");
            $cache->forgetGroup("translations.{$translation->group}.nl");
            $cache->forget(); // Also clear the legacy full cache for backward compatibility
        });
    }
}
