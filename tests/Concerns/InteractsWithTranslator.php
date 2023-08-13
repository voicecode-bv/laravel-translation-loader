<?php

namespace Esign\TranslationLoader\Tests\Concerns;

use Esign\TranslationLoader\Translator;

trait InteractsWithTranslator
{
    protected Translator $translator;

    protected function setUpInteractsWithTranslator(): void
    {
        $this->translator = app(Translator::class);
    }

    protected function resetInternalTranslatorCache(): void
    {
        $this->translator->setLoaded([]);
    }
}
