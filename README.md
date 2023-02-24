# Laravel API Resource Generator Package

This package will help you to generate API resources for your Laravel project.

## Installation

You can install the package via composer:

```bash
composer require alibori/laravel-api-resource-generator
```

## Usage

``` bash
php artisan alibori:api-resource <model-name>
```

## Publish config file

``` bash
php artisan vendor:publish --provider="Alibori\LaravelApiResourceGenerator\LaravelApiResourceGeneratorServiceProvider" --tag="config"
```
