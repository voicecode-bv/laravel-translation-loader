<?php

namespace Esign\TranslationLoader\Tests;

use Illuminate\Support\Facades\Config;

class TranslationTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_a_file_translation()
    {
        $this->assertEquals('en value', trans('file.key'));
        $this->assertEquals('nested key', trans('file.nested-key.title'));
        $this->assertEquals('this is a nested key', trans('file.nested-key.message'));
    }

    /** @test */
    public function it_can_retrieve_a_file_translation_for_a_locale()
    {
        $this->assertEquals('nl value', trans('file.key', [], 'nl'));
        $this->assertEquals('geneste key', trans('file.nested-key.title', [], 'nl'));
        $this->assertEquals('dit is een geneste key', trans('file.nested-key.message', [], 'nl'));
    }

    /** @test */
    public function it_can_retrieve_a_database_translation()
    {
        $this->createTranslation('*', 'database.key', ['value_en' => 'test en']);

        $this->assertEquals('test en', trans('database.key'));
    }

    /** @test */
    public function it_can_retrieve_a_database_translation_for_a_locale()
    {
        $this->createTranslation('*', 'database.key', [
            'value_en' => 'test en',
            'value_nl' => 'test nl',
        ]);

        $this->assertEquals('test nl', trans('database.key', [], 'nl'));
    }

    /** @test */
    public function it_can_prefer_a_database_translation_over_a_file_translation()
    {
        $this->createTranslation('*', 'file.key', ['value_en' => 'en value database']);

        $this->assertEquals('en value database', trans('file.key'));
    }

    /** @test */
    public function it_can_retrieve_a_database_translation_using_a_fallback()
    {
        $this->createTranslation('*', 'database.key', [
            'value_en' => 'test en',
            'value_nl' => null,
        ]);

        $this->assertEquals('test en', trans('database.key', [], 'nl'));
    }

    /** @test */
    public function it_can_retrieve_a_database_translation_as_a_string_if_the_full_nested_key_is_given()
    {
        $this->createTranslation('validation', 'test', ['value_en' => 'test en']);

        $this->assertEquals('test en', trans('validation.test'));
    }

    /** @test */
    public function it_can_retrieve_a_database_translation_as_an_array_if_a_part_of_the_nested_key_is_given()
    {
        $this->createTranslation('validation', 'testA', ['value_en' => 'testA en']);
        $this->createTranslation('validation', 'testB', ['value_en' => 'testB en']);

        $this->assertEquals(
            ['testA' => 'testA en', 'testB' => 'testB en'],
            trans('validation')
        );
    }

    /** @test */
    public function it_can_create_a_translation_entry_when_the_key_does_not_exist()
    {
        trans('this-key-does-not-exist');

        $this->assertDatabaseHas('translations', [
            'key' => 'this-key-does-not-exist',
            'group' => '*',
        ]);
    }

    /** @test */
    public function it_wont_create_multiple_translation_entries_when_the_translation_was_called_multiple_times()
    {
        trans('this-key-does-not-exist');
        trans('this-key-does-not-exist');

        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_wont_create_entries_when_the_translator_config_is_null()
    {
        Config::set('translation-loader.translator', null);

        trans('this-key-does-not-exist');

        $this->assertDatabaseCount('translations', 0);
    }
}
