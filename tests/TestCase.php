<?php

namespace Esign\TranslationLoader\Tests;

use Esign\TranslationLoader\Models\Translation;
use Esign\TranslationLoader\Tests\Concerns\InteractsWithTranslator;
use Esign\TranslationLoader\Tests\Concerns\MakesQueryCountAssertions;
use Esign\TranslationLoader\TranslationLoaderServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits(): void
    {
        $uses = parent::setUpTraits();

        if (isset($uses[InteractsWithTranslator::class])) {
            $this->setUpInteractsWithTranslator();
        }

        if (isset($uses[MakesQueryCountAssertions::class])) {
            $this->setUpMakesQueryCountAssertions();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [TranslationLoaderServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.lang'] = __DIR__ . "/fixtures/lang";

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('group')->default('*');
            $table->text('value_en')->nullable();
            $table->text('value_nl')->nullable();
            $table->timestamps();

            $table->unique(['key', 'group']);
        });
    }

    protected function createTranslation(string $group, string $key, array $attributes): Translation
    {
        return Translation::create(array_merge(
            ['group' => $group, 'key' => $key],
            $attributes
        ));
    }
}
