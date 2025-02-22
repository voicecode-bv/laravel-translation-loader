<?php

namespace Esign\TranslationLoader\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Esign\TranslationLoader\Exceptions\InvalidConfiguration;
use Esign\TranslationLoader\Tests\Models\CustomTranslationModel;
use Esign\TranslationLoader\Tests\Models\InvalidTranslationModel;
use Esign\TranslationLoader\Tests\TestCase;
use Esign\TranslationLoader\TranslationLoaderServiceProvider;
use Illuminate\Support\Facades\Config;

final class CustomTranslationModelTest extends TestCase
{
    #[Test]
    public function it_can_use_a_custom_model_to_load_database_translations(): void
    {
        Config::set('translation-loader.model', CustomTranslationModel::class);
        CustomTranslationModel::create([
            'key' => 'database.key',
            'group' => '*',
            'value_en' => 'test en',
        ]);

        $this->assertEquals('test en', trans('database.key'));
    }

    #[Test]
    public function it_will_throw_an_exception_when_the_model_does_not_implement_the_redirect_contract(): void
    {
        Config::set('translation-loader.model', InvalidTranslationModel::class);
        $this->expectException(InvalidConfiguration::class);

        TranslationLoaderServiceProvider::getConfiguredModel();
    }
}
