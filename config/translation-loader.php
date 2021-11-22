<?php

return [
    /**
     * These loaders will load translations from different sources.
     * You can use any class that implements the TranslationLoaderContract.
     */
    'loaders' => [
        \Esign\TranslationLoader\Loaders\DatabaseLoader::class,
    ],

    /**
     * This is the loader that combines all of the other loaders together.
     * This class overrides Laravel's default `translation.loader`.
     */
    'aggregate_loader' => \Esign\TranslationLoader\Loaders\AggregateLoader::class,

    /**
     * This is the model that will be used by the DatabaseLoader.
     */
    'model' => \Esign\TranslationLoader\Models\Translation::class,

    /**
     * The key that will be used to cache the database translations.
     */
    'cache_key' => 'translations',

    /**
     * The amount of seconds the database translations will be cached for.
     */
    'cache_remember' => 15,

    /**
     * This translator creates new entries to the database if the translation could not be found.
     * In case you do not want this behaviour, you may set this to null.
     */
    'translator' => \Esign\TranslationLoader\Translator::class,
];