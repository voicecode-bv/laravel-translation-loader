<?php

namespace Esign\TranslationLoader\Loaders;

use Esign\TranslationLoader\Contracts\TranslationLoaderContract;
use Esign\TranslationLoader\TranslationLoaderServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class DatabaseLoader implements TranslationLoaderContract
{
    public function loadTranslations(string $locale, string $group, string $namespace = null): array
    {
        $configuredModel = TranslationLoaderServiceProvider::getConfiguredModel();
        $translations = Cache::remember(
            config('translation-loader.cache_key', 'translations'),
            config('translation-loader.cache_remember', 15),
            fn () => $configuredModel::get()
        );

        return $translations
            ->where('group', $group)
            ->reduce(function ($translationsArray, Model $model) use ($locale, $group) {
                $translation = $model->getTranslationWithFallback('value', $locale);

                if ($group === '*') {
                    $translationsArray[$model->key] = $translation;
                } elseif ($group !== '*') {
                    Arr::set($translationsArray, $model->key, $translation);
                }

                return $translationsArray;
            }) ?? [];
    }
}