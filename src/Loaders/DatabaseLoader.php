<?php

namespace Esign\TranslationLoader\Loaders;

use Esign\TranslationLoader\Contracts\TranslationLoaderContract;
use Esign\TranslationLoader\TranslationLoaderServiceProvider;
use Esign\TranslationLoader\TranslationsCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class DatabaseLoader implements TranslationLoaderContract
{
    public function __construct(protected TranslationsCache $translationsCache)
    {
    }

    public function loadTranslations(string $locale, string $group, ?string $namespace = null): array
    {
        if (config('translation-loader.skip_database', false)) {
            return [];
        }

        try {
            $configuredModel = TranslationLoaderServiceProvider::getConfiguredModel();
            
            $cacheKey = $this->getCacheKey($locale, $group);
            $translations = $this->translationsCache->rememberForGroup($cacheKey, function () use ($configuredModel, $group) {
                return $configuredModel::where('group', $group)->get();
            });

            return $translations
                ->reduce(function ($translationsArray, Model $model) use ($locale, $group) {
                    $translation = $model->getTranslationWithFallback('value', $locale);

                    if ($group === '*') {
                        $translationsArray[$model->key] = $translation;
                    } elseif ($group !== '*') {
                        Arr::set($translationsArray, $model->key, $translation);
                    }

                    return $translationsArray;
                }) ?? [];
        } catch (Throwable $e) {
            if (config('translation-loader.log_database_errors', true)) {
                Log::warning('Failed to load translations from database', [
                    'error' => $e->getMessage(),
                    'locale' => $locale,
                    'group' => $group,
                ]);
            }

            return [];
        }
    }

    protected function getCacheKey(string $locale, string $group): string
    {
        return "translations.{$group}.{$locale}";
    }
}
