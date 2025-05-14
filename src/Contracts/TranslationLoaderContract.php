<?php

namespace Esign\TranslationLoader\Contracts;

interface TranslationLoaderContract
{
    public function loadTranslations(string $locale, string $group, ?string $namespace = null): array;
}
