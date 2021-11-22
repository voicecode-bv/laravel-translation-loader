<?php

namespace Esign\TranslationLoader;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Translation\Translator as TranslationTranslator;

class Translator extends TranslationTranslator
{
    public function get($key, array $replace = [], $locale = null, $fallback = true): string|array
    {
        $line = parent::get($key, $replace, $locale, $fallback);

        if ($line === $key) {
            $this->createConfiguredModelEntry($key);
        }

        return $line;
    }

    protected function createConfiguredModelEntry(string $key): Model
    {
        $configuredModel = TranslationLoaderServiceProvider::getConfiguredModel();

        return $configuredModel::updateOrCreate(['key' => $key, 'group' => '*']);
    }
}