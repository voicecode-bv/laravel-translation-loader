<?php

namespace Esign\TranslationLoader\Tests\Feature;

use Esign\TranslationLoader\Models\Translation;
use Esign\TranslationLoader\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        Config::set('app.locale', 'nl');
        Config::set('app.fallback_locale', 'en');
        $this->createTranslation('*', 'database.key', [
            'value_en' => 'test en',
            'value_nl' => null,
        ]);

        $this->assertEquals('test en', trans('database.key', [], 'nl'));
    }

    /** @test */
    public function it_can_retrieve_a_database_translation_as_a_string_if_the_full_nested_key_is_given()
    {
        $this->createTranslation('errors', 'test', ['value_en' => 'test en']);

        $this->assertEquals('test en', trans('errors.test'));
    }

    /** @test */
    public function it_can_retrieve_a_database_translation_as_an_array_if_a_part_of_the_nested_key_is_given()
    {
        $this->createTranslation('errors', 'testA', ['value_en' => 'testA en']);
        $this->createTranslation('errors', 'testB', ['value_en' => 'testB en']);

        $this->assertEquals(
            ['testA' => 'testA en', 'testB' => 'testB en'],
            trans('errors')
        );
    }

    /** @test */
    public function it_can_create_a_translation_entry_when_the_key_does_not_exist()
    {
        app('translator')->createMissingTranslations();
        trans('this-key-does-not-exist');

        $this->assertDatabaseHas(Translation::class, [
            'key' => 'this-key-does-not-exist',
            'group' => '*',
        ]);
    }

    /** @test */
    public function it_wont_create_multiple_translation_entries_when_the_translation_was_called_multiple_times()
    {
        app('translator')->createMissingTranslations();
        trans('this-key-does-not-exist');
        trans('this-key-does-not-exist');

        $this->assertDatabaseCount(Translation::class, 1);
    }

    /** @test */
    public function it_wont_create_internal_laravel_translation_keys_when_validating()
    {
        app('translator')->createMissingTranslations();
        $validator = Validator::make(
            data: ['email' => null],
            rules: ['email' => 'required'],
        );

        try {
            $validator->validated();
        } catch (ValidationException) {
        };

        $this->assertDatabaseCount(Translation::class, 0);
        $this->assertDatabaseMissing(Translation::class, ['key' => 'validation.values.email.']);
        $this->assertDatabaseMissing(Translation::class, ['key' => 'validation.custom']);
        $this->assertDatabaseMissing(Translation::class, ['key' => 'validation.custom.email.required']);
        $this->assertDatabaseMissing(Translation::class, ['key' => 'validation.attributes']);
    }

    /** @test */
    public function it_can_pass_the_app_locale_to_the_missing_key_callback_when_no_locale_is_given()
    {
        app()->setLocale('en');
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            return $locale;
        });

        $this->assertEquals('en', trans('translation-key'));
    }

    /** @test */
    public function it_can_pass_the_correct_locale_to_the_missing_key_callback()
    {
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            return $locale;
        });

        $this->assertEquals('nl', trans('translation-key', [], 'nl'));
    }

    /** @test */
    public function it_can_pass_the_correct_key_to_the_missing_key_callback()
    {
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            return $key;
        });

        $this->assertEquals('translation-key', trans('translation-key'));
    }

    /** @test */
    public function it_can_set_a_missing_key_callback_and_return_a_custom_value()
    {
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            return 'Custom value';
        });

        $this->assertEquals('Custom value', trans('this-key-does-not-exist'));
    }

    /** @test */
    public function it_can_set_a_missing_key_callback_and_return_a_custom_value_with_replacements()
    {
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            return 'Custom value :value';
        });

        $this->assertEquals('Custom value abc', trans('this-key-does-not-exist', ['value' => 'abc']));
    }

    /** @test */
    public function it_wont_call_the_missing_key_callback_when_the_translation_exists_with_a_null_value()
    {
        $this->createTranslation('*', 'translation-key', ['value_en' => null]);
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            return 'Custom';
        });

        $this->assertEquals('translation-key', trans('translation-key'));
    }

    /** @test */
    public function it_wont_call_the_missing_key_callback_when_the_translation_exists_in_a_json_file()
    {
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            $this->fail('The missing key callback was called unexpectedly.');
        });

        $this->assertEquals('Hello world', trans('Hello world'));
    }

    /** @test */
    public function it_wont_call_the_missing_key_callback_when_the_translation_exists_in_a_php_file()
    {
        app('translator')->setMissingKeyCallback(function (string $key, string $locale) {
            $this->fail('The missing key callback was called unexpectedly.');
        });

        $this->assertEquals('en value', trans('file.key'));
    }
}
