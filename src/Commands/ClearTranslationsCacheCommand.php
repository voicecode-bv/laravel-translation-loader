<?php

namespace Esign\TranslationLoader\Commands;

use Esign\TranslationLoader\TranslationsCache;
use Illuminate\Console\Command;

class ClearTranslationsCacheCommand extends Command
{
    protected $signature = 'translations:clear-cache';
    protected $description = 'Clears the translations cache.';

    public function handle(TranslationsCache $translationsCache): void
    {
        $translationsCache->forget();

        $this->info('Successfully cleared the translations cache.');
    }
}
