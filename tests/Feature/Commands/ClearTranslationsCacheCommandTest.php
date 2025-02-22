<?php

namespace Esign\TranslationLoader\Tests\Feature\Commands;

use PHPUnit\Framework\Attributes\Test;
use Esign\TranslationLoader\Commands\ClearTranslationsCacheCommand;
use Esign\TranslationLoader\Tests\Concerns\InteractsWithTranslator;
use Esign\TranslationLoader\Tests\Concerns\MakesQueryCountAssertions;
use Esign\TranslationLoader\Tests\TestCase;

class ClearTranslationsCacheCommandTest extends TestCase
{
    use InteractsWithTranslator;
    use MakesQueryCountAssertions;

    #[Test]
    public function it_can_clear_the_translations_cache(): void
    {
        // Request the translation so the database translations get queried and cached.
        // This causes the first database query.
        trans('translation-key');

        $this->artisan(ClearTranslationsCacheCommand::class);

        // Reset the internal cache of the translator, so we can make assertions against our own database query cache.
        $this->resetInternalTranslatorCache();

        // Request the translation so the database translations get queried and cached once again.
        // This causes the second database query.
        trans('translation-key');

        $this->assertQueryCount(2);
    }
}
