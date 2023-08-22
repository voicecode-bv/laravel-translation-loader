<?php

namespace Esign\TranslationLoader;

use Closure;
use Esign\TranslationLoader\Models\Translation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator as TranslationTranslator;

class Translator extends TranslationTranslator
{
    protected ?Closure $missingKeyCallback = null;

    public function setMissingKeyCallback(Closure $callback): void
    {
        $this->missingKeyCallback = $callback;
    }

    public function createMissingTranslations(): void
    {
        $this->setMissingKeyCallback(function (string $key, string $locale) {
            if ($this->shouldSkipTranslationCreation($key)) {
                return $key;
            }

            $configuredModelClass = TranslationLoaderServiceProvider::getConfiguredModel();
            $translation = new $configuredModelClass();
            $translation->group = '*';
            $translation->key = $key;
            $translation->save();

            $this->addLine(
                namespace: '*',
                group: '*',
                locale: $locale,
                key: $key,
                value: $translation->getTranslationWithoutFallback('value', $locale),
            );

            return $key;
        });
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true): string|array
    {
        $locale = $locale ?: $this->locale;
        if (! $this->hasLine($key, $locale) && ! is_null($this->missingKeyCallback)) {
            return $this->makeReplacements(
                call_user_func($this->missingKeyCallback, $key, $locale),
                $replace
            );
        }

        return parent::get($key, $replace, $locale, $fallback);
    }

    protected function addLine(string $namespace, string $group, string $locale, string $key, mixed $value): void
    {
        $this->loaded[$namespace][$group][$locale][$key] = $value;
    }

    protected function hasLine(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?: $this->locale;

        // For JSON translations, there is only one file per locale, so we will simply load
        // that file and then we will be ready to check the array for the key. These are
        // only one level deep so we do not need to do any fancy searching through it.
        $this->load('*', '*', $locale);

        if (Arr::has($this->loaded['*']['*'][$locale] ?? [], $key)) {
            return true;
        }

        // If we can't find a translation for the JSON key, we will attempt to translate it
        // using the typical translation file. This way developers can always just use a
        // helper such as __ instead of having to pick between trans or __ with views.
        [$namespace, $group, $item] = $this->parseKey($key);
        $this->load($namespace, $group, $locale);

        if (Arr::has($this->loaded[$namespace][$group][$locale] ?? [], $item)) {
            return true;
        }

        // In any other case, the translation is not loaded
        return false;
    }

    protected function shouldSkipTranslationCreation(string $key): bool
    {
        return Str::contains($key, [
            // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Validation/Concerns/FormatsMessages.php#L445
            'validation.values',
            // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Validation/Concerns/FormatsMessages.php#L38
            // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Validation/Concerns/FormatsMessages.php#L159
            'validation.custom',
            // https://github.com/laravel/framework/blob/10.x/src/Illuminate/Validation/Concerns/FormatsMessages.php#L309
            'validation.attributes',
        ]);
    }
}
