# ModelPlus

ModelPlus is an intuitive UI package for Laravel that provides a powerful and user-friendly interface for browsing and managing your Eloquent models. It automatically discovers your models and creates a dynamic, interactive dashboard for viewing and managing your application's data.

It's still a work in progress, but it's already useful for viewing your models.

## Features

- **Automatic Model Discovery**: Automatically scans and discovers all Eloquent models in your application
- **Interactive Dashboard**: Clean, modern UI built with Tailwind CSS and Alpine.js
- **Smart Relationship Handling**: 
  - Automatically detects and displays model relationships
  - Interactive hover cards for related models
  - Smart navigation between related models
- **Intelligent Display**: 
  - Automatically determines the best display column for each model
  - Smart formatting for different data types
  - Handles null values and boolean states elegantly
- **Advanced Table Features**:
  - Sortable columns
  - Real-time search
  - Pagination
  - Sticky first column
  - Responsive design
- **Security**:
  - Configurable authentication requirements
  - Debug mode protection
  - Middleware-based access control



## Configuration

The package can be configured via the `config/modelplus.php` file:

- Define custom model paths
- Configure route prefix and middleware
- Set pagination preferences
- Specify hidden fields
- Control authentication requirements

## Security

By default, ModelPlus is only accessible in debug mode or to users with the `access-modelplus` permission. This behavior can be customized in the configuration file.

## Usage

Once installed, navigate to `/modelplus` in your browser to access the dashboard. The interface will automatically display all discovered models in a sidebar, allowing you to:

- Browse records for each model
- View relationships between models
- Sort and search records
- Navigate through related data
- View detailed information via hover cards

## Local Development

For local development:

1. Add this to your `composer.json`:






# Local Development
## Installation

1. Install the package via Composer:

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
