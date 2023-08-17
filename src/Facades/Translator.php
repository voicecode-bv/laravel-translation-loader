<?php

namespace Esign\TranslationLoader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setMissingKeyCallback(Closure $callback)
 * @method static void createMissingTranslations()
 */
class Translator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'translator';
    }
}
