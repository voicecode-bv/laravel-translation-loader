<?php

namespace Esign\TranslationLoader\Tests\Models;

use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Database\Eloquent\Model;

class CustomTranslationModel extends Model
{
    use UnderscoreTranslatable;

    protected $guarded = [];
    protected $table = 'translations';
}