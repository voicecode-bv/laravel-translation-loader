# Deployment Guide for Laravel Translation Loader

This guide explains how to safely deploy applications using the Laravel Translation Loader package without encountering database connection issues during deployment.

## The Problem

During deployments, the translation loader may attempt to fetch translations from the database before:
- Migrations have been run
- The database is available
- The translations table exists

This can cause deployment scripts to fail with database connection errors.

## The Solution

This package now includes deployment-safe features that prevent database access issues during deployments.

## Configuration Options

### Environment Variables

```bash
# Skip database loading entirely (useful during deployments)
TRANSLATION_LOADER_SKIP_DATABASE=true|false

# Control database error logging
TRANSLATION_LOADER_LOG_DB_ERRORS=true|false
```

### Config File Options

In `config/translation-loader.php`:

```php
return [
    // ... other config

    /**
     * Skip loading translations from the database.
     * This is useful during deployments when the database might not be available.
     */
    'skip_database' => env('TRANSLATION_LOADER_SKIP_DATABASE', false),

    /**
     * Whether to log database connection errors when attempting to load translations.
     * Set to false to suppress warnings during deployments.
     */
    'log_database_errors' => env('TRANSLATION_LOADER_LOG_DB_ERRORS', true),
];
```

## Deployment Script Examples

### Basic Deployment Script

```bash
#!/bin/bash

# 1. Enable maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. IMPORTANT: Skip database during initial setup
export TRANSLATION_LOADER_SKIP_DATABASE=true

# 5. Run migrations (database might not be ready yet)
php artisan migrate --force

# 6. Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Re-enable database translations
export TRANSLATION_LOADER_SKIP_DATABASE=false

# 8. Clear translation cache to ensure fresh data
php artisan translations:clear-cache

# 9. Disable maintenance mode
php artisan up
```

### Docker Deployment

```dockerfile
# In your Dockerfile or docker-compose.yml
ENV TRANSLATION_LOADER_SKIP_DATABASE=true

# Run your build steps...

# In your entrypoint script:
#!/bin/sh
php artisan migrate --force
php artisan config:cache

# After migrations are complete
export TRANSLATION_LOADER_SKIP_DATABASE=false
php artisan translations:clear-cache

# Start your application
php-fpm
```

### CI/CD Pipeline (GitHub Actions Example)

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
      
      - name: Run migrations with database skip
        env:
          TRANSLATION_LOADER_SKIP_DATABASE: true
        run: |
          php artisan migrate --force
          php artisan config:cache
      
      - name: Enable database translations
        run: |
          export TRANSLATION_LOADER_SKIP_DATABASE=false
          php artisan translations:clear-cache
```

### Laravel Forge Deployment Script

```bash
cd /home/forge/your-site.com

# Put the application into maintenance mode
php artisan down

# Pull the latest changes
git pull origin main

# Install/update composer dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Skip database during cache rebuild
export TRANSLATION_LOADER_SKIP_DATABASE=true

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Re-enable database translations
export TRANSLATION_LOADER_SKIP_DATABASE=false

# Clear translation cache
php artisan translations:clear-cache

# Restart PHP-FPM
sudo -S service php8.2-fpm reload

# Exit maintenance mode
php artisan up
```

## Laravel Vapor Deployment

For Laravel Vapor, add to your `vapor.yml`:

```yaml
environments:
  production:
    build:
      - 'TRANSLATION_LOADER_SKIP_DATABASE=true php artisan config:cache'
      - 'TRANSLATION_LOADER_SKIP_DATABASE=true php artisan route:cache'
    deploy:
      - 'php artisan migrate --force'
      - 'php artisan translations:clear-cache'
```

## Zero-Downtime Deployments

For zero-downtime deployments, the package automatically handles database unavailability:

1. **Automatic Fallback**: If the database is unavailable, the package automatically falls back to file-based translations
2. **Graceful Error Handling**: Database errors are caught and logged (if configured)
3. **Lazy Loading**: Only requested translation groups are loaded, reducing deployment impact

## Best Practices

1. **Always skip database during initial deployment steps** when running migrations or building caches
2. **Clear translation cache after deployment** to ensure fresh translations are loaded
3. **Use environment variables** rather than modifying config files during deployment
4. **Test your deployment script** in a staging environment first
5. **Monitor logs** for translation loading errors if `log_database_errors` is enabled

## Troubleshooting

### Deployment still fails with database errors

Ensure you're setting the environment variable before running any artisan commands:

```bash
# Correct
export TRANSLATION_LOADER_SKIP_DATABASE=true
php artisan migrate

# Incorrect (variable not exported)
TRANSLATION_LOADER_SKIP_DATABASE=true
php artisan migrate
```

### Translations not updating after deployment

Clear the translation cache after deployment:

```bash
php artisan translations:clear-cache
```

### Silent failures during deployment

Enable error logging to debug issues:

```bash
export TRANSLATION_LOADER_LOG_DB_ERRORS=true
```

## Performance Benefits

The new deployment-safe implementation also provides performance improvements:

- **Lazy Loading**: Only loads translation groups when needed
- **Per-Group Caching**: More efficient memory usage
- **Reduced Database Queries**: Caches database availability checks
- **Graceful Degradation**: Falls back to file translations if database is unavailable

## Migration from Older Versions

If upgrading from an older version of this package:

1. Publish the new config file:
   ```bash
   php artisan vendor:publish --provider="Esign\TranslationLoader\TranslationLoaderServiceProvider" --tag="config" --force
   ```

2. Update your deployment scripts to use the new environment variables

3. Clear all caches after upgrading:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan translations:clear-cache
   ```

## Support

For issues or questions about deployment configuration, please refer to the [main README](README.md) or create an issue on GitHub.