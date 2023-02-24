# Laravel API Resource Generator Package

This package will help you to generate API resources for your Laravel project.

## Installation

You can install the package via composer:

```bash
composer require alibori/laravel-api-resource-generator
```

## Usage

All you need to do is to run the following command:

``` bash
php artisan alibori:api-resource <model-name>
```

This command will generate a new resource for the given model name with the properties defined in the model.

## Publish config file

If you want to publish the config file to change the default namespace for the generated resources and the directory where the resources will be generated, you can run the following command:

``` bash
php artisan vendor:publish --provider="Alibori\LaravelApiResourceGenerator\LaravelApiResourceGeneratorServiceProvider" --tag="config"
```
