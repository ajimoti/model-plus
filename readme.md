
## For local development:
1. Add this to your `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../modelplus"  // Relative path to your package
        }
    ],
    "require": {
        // ... other requirements ...
        "vendor/modelplus": "@dev"
    }
}
```
2. Run `composer install`
3. Run `php artisan vendor:publish --provider="Vendor\ModelPlus\ModelPlusServiceProvider"`


## For production:
1. Run `composer require vendor/modelplus`
2. Run `php artisan vendor:publish --provider="Vendor\ModelPlus\ModelPlusServiceProvider"`
# model-plus
