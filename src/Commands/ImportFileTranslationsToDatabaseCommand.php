<?php

namespace Esign\TranslationLoader\Commands;

use Esign\TranslationLoader\Actions\ImportFileTranslationsToDatabaseAction;
use Illuminate\Console\Command;

class ImportFileTranslationsToDatabaseCommand extends Command
{
    protected $signature = 'translations:import-files-to-database {--locales=} {--overwrite}';
    protected $description = 'Imports file translations to the database.';

    public function handle(ImportFileTranslationsToDatabaseAction $importFileTranslationsToDatabaseAction): int
    {
        $affectedRecords = $importFileTranslationsToDatabaseAction->handle(
            locales: explode(',', $this->option('locales')),
            overwrite: (bool) $this->option('overwrite'),
        );

        $this->info("Successfully imported translations, affected records: {$affectedRecords}.");

        return self::SUCCESS;
    }
}
