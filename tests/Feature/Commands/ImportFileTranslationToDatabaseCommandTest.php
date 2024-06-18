<?php

namespace Esign\TranslationLoader\Tests\Feature\Commands;

use Esign\TranslationLoader\Commands\ImportFileTranslationsToDatabaseCommand;
use Esign\TranslationLoader\Models\Translation;
use Esign\TranslationLoader\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportFileTranslationToDatabaseCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_import_translations()
    {
        $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en,nl']);

        $this->assertDatabaseHas(Translation::class, [
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Hello world',
            'value_nl' => 'Hallo wereld',
        ]);
    }

    /** @test */
    public function it_can_import_translations_for_specific_locales()
    {
        $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en']);

        $this->assertDatabaseHas(Translation::class, [
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Hello world',
            'value_nl' => null,
        ]);
    }

    /** @test */
    public function it_wont_overwrite_existing_translations_when_the_overwrite_flag_was_not_given()
    {
        Translation::create([
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Goodbye world',
            'value_nl' => 'Tot ziens wereld',
        ]);

        $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en,nl']);

        $this->assertDatabaseHas(Translation::class, [
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Goodbye world',
            'value_nl' => 'Tot ziens wereld',
        ]);
    }

    /** @test */
    public function it_can_overwrite_existing_translations()
    {
        Translation::create([
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Goodbye world',
            'value_nl' => 'Tot ziens wereld',
        ]);

        $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en,nl', '--overwrite' => true]);

        $this->assertDatabaseHas(Translation::class, [
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Hello world',
            'value_nl' => 'Hallo wereld',
        ]);
    }

    /** @test */
    public function it_wont_overwrite_existing_translations_for_locales_that_were_not_specified()
    {
        Translation::create([
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Goodbye world',
            'value_nl' => 'Tot ziens wereld',
        ]);

        $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en', '--overwrite' => true]);

        $this->assertDatabaseHas(Translation::class, [
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Hello world',
            'value_nl' => 'Tot ziens wereld',
        ]);
    }

    /** @test */
    public function it_can_report_the_affected_records()
    {
        $command = $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en']);

        $command->expectsOutputToContain('Successfully imported translations, affected records: 1.');
        $command->assertSuccessful();
    }

    /** @test */
    public function it_can_report_the_affected_records_when_a_translation_is_already_present()
    {
        Translation::create([
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Hello world',
        ]);

        $command = $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en']);

        $command->expectsOutputToContain('Successfully imported translations, affected records: 0.');
        $command->assertSuccessful();
    }

    /** @test */
    public function it_can_report_affected_records_when_the_overwrite_flag_is_given()
    {
        $command = $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en', '--overwrite' => true]);

        $command->expectsOutputToContain('Successfully imported translations, affected records: 1.');
        $command->assertSuccessful();
    }

    /** @test */
    public function it_can_report_affected_records_when_the_overwrite_flag_is_given_and_a_translation_is_already_present()
    {
        Translation::create([
            'group' => '*',
            'key' => 'Hello world',
            'value_en' => 'Hello world',
        ]);

        $command = $this->artisan(ImportFileTranslationsToDatabaseCommand::class, ['--locales' => 'en', '--overwrite' => true]);

        $command->expectsOutputToContain('Successfully imported translations, affected records: 1.');
        $command->assertSuccessful();
    }
}
