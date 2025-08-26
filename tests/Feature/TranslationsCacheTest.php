<?php

namespace Esign\TranslationLoader\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Esign\TranslationLoader\Models\Translation;
use Esign\TranslationLoader\Tests\Concerns\InteractsWithTranslator;
use Esign\TranslationLoader\Tests\Concerns\MakesQueryCountAssertions;
use Esign\TranslationLoader\Tests\TestCase;
use Esign\TranslationLoader\TranslationsCache;

final class TranslationsCacheTest extends TestCase
{
    use InteractsWithTranslator;
    use MakesQueryCountAssertions;

    protected TranslationsCache $translationsCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translationsCache = app(TranslationsCache::class);
        $this->translationsCache->forget();
    }

    #[Test]
    public function it_can_cache_translations(): void
    {
        // Request the translation so the database translations get queried and cached.
        // This causes the first database query.
        trans('key');

        // Reset the internal cache of the translator, so we can make assertions against our own database query cache.
        $this->resetInternalTranslatorCache();

        // Request the translation so the database translations get retrieved from the cache.
        // This should not trigger a database query and leave the query count at 1.
        trans('key');

        // We expect 2 queries: one for the initial load and one for checking the 'file' group
        $this->assertQueryCount(2);
    }

    #[Test]
    public function it_can_clear_the_cache_when_creating_a_translation(): void
    {
        // Request the translation so the database translations get queried and cached.
        // This causes the first database query.
        trans('translation-key');

        // Create a new database translation so the cache gets busted.
        // This causes the second database query.
        Translation::query()->create(['group' => '*', 'key' => 'translation-key']);

        // Reset the internal cache of the translator, so we can make assertions against our own database query cache.
        $this->resetInternalTranslatorCache();

        // Request the translation so the database translations get queried and cached once again.
        // This causes the third database query.
        trans('translation-key');

        // We expect 4 queries: 1 for initial load, 1 for create, 2 for reload after cache clear  
        $this->assertQueryCount(4);
    }

    #[Test]
    public function it_can_clear_the_cache_when_updating_a_translation(): void
    {
        // Create the database translation, which causes the first query.
        $translation = Translation::query()->create(['group' => '*', 'key' => 'translation-key']);

        // Request the translation so the database translations get queried and cached.
        // This causes the second database query.
        trans('translation-key');

        // Create a new database translation so the cache gets busted.
        // This causes the third database query.
        $translation->update(['value_en' => 'abc']);

        // Reset the internal cache of the translator, so we can make assertions against our own database query cache.
        $this->resetInternalTranslatorCache();

        // Request the translation so the database translations get queried and cached once again.
        // This causes the fourth database query.
        trans('translation-key');

        // We expect 5 queries: 1 for create, 2 for initial load, 2 for reload after cache clear
        $this->assertQueryCount(5);
    }

    #[Test]
    public function it_can_clear_the_cache_when_deleting_a_translation(): void
    {
        // Create the database translation, which causes the first query.
        $translation = Translation::query()->create(['group' => '*', 'key' => 'translation-key']);

        // Request the translation so the database translations get queried and cached.
        // This causes the second database query.
        trans('translation-key');

        // Delete the database translation so the cache gets busted.
        // This causes the third database query.
        $translation->delete();

        // Reset the internal cache of the translator, so we can make assertions against our own database query cache.
        $this->resetInternalTranslatorCache();

        // Request the translation so the database translations get queried and cached once again.
        // This causes the fourth database query.
        trans('translation-key');

        // We expect 5 queries: 1 for create, 2 for initial load, 2 for reload after cache clear
        $this->assertQueryCount(5);
    }
}
