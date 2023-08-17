<?php

namespace Esign\TranslationLoader\Tests\Feature;

use Esign\TranslationLoader\Loaders\AggregateLoader;
use Esign\TranslationLoader\Tests\TestCase;
use Esign\TranslationLoader\Translator;

class TranslationLoaderServiceProviderTest extends TestCase
{
    /** @test */
    public function it_can_override_the_translator_binding_in_the_container()
    {
        $this->assertInstanceOf(Translator::class, $this->app->make('translator'));
    }

    /** @test */
    public function it_can_override_the_translation_loader_biding_in_the_container()
    {
        $this->assertInstanceOf(AggregateLoader::class, $this->app->make('translation.loader'));
    }
}
