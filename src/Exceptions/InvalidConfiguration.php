<?php

namespace Esign\TranslationLoader\Exceptions;

use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidConfiguration extends Exception
{
    public static function invalidModel(string $className): self
    {
        return new static(sprintf(
            'The configured model class `%s` does not use the `%s` trait or does not extend the `%s` class.',
            $className,
            UnderscoreTranslatable::class,
            Model::class,
        ));
    }
}
