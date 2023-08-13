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

    'cache' => [
        /**
         * The key that will be used to cache the database translations.
         */
        'key' => 'esign.laravel-translation-loader.translations',

        /**
         * The duration for which database translations will be cached.
         */
        'ttl' => \DateInterval::createFromDateString('24 hours'),

        /**
         * The cache store to be used for database translations.
         * Use null to utilize the default cache store from the cache.php config file.
         * To disable caching, you can use the 'array' store.
         */
        'store' => null,
    ],

    /**
     * Configuration for the custom translator class that handles missing translation keys.
     * This class overrides Laravel's default `translator` binding.
     */
    'translator' => \Esign\TranslationLoader\Translator::class,
];
