<?php

namespace Esign\TranslationLoader\Models;

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
}