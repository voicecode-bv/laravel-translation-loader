<?php

namespace Esign\TranslationLoader\Loaders;

use Esign\TranslationLoader\Contracts\TranslationLoaderContract;
use Illuminate\Translation\FileLoader;

class AggregateLoader extends FileLoader implements TranslationLoaderContract
{
    public function load($locale, $group, $namespace = null): array
    {
        $fileTranslations = parent::load($locale, $group, $namespace);

        $aggregateTranslations = $this->loadTranslations($locale, $group, $namespace);

        return array_merge($fileTranslations, $aggregateTranslations);
    }

    public function loadTranslations(string $locale, string $group, string $namespace = null): array
    {
        return collect(config('translation-loader.loaders'))
            ->map(fn ($className) => app($className))
            ->flatMap(function (TranslationLoaderContract $translationLoader) use ($locale, $group, $namespace) {
                return $translationLoader->loadTranslations($locale, $group, $namespace);
            })
            ->toArray();
    }
}