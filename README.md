# Laravel API Resource Generator Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alibori/laravel-api-resource-generator.svg?style=flat-square)](https://packagist.org/packages/alibori/laravel-api-resource-generator)
![Tests Passed](https://github.com/alibori/laravel-api-resource-generator/actions/workflows/run-tests.yml/badge.svg?event=push)
[![Total Downloads](https://img.shields.io/packagist/dt/alibori/laravel-api-resource-generator.svg?style=flat-square)](https://packagist.org/packages/alibori/laravel-api-resource-generator)

This package will help you to generate API resources for your Laravel project.

## Installation

You can install the package via composer:

```bash
composer require alibori/laravel-api-resource-generator
```

## Usage

All you need to do is to run the following command:

``` bash
php artisan api-resource:generate <model-name>
```

This command will generate a new resource for the given model name with the properties defined in the model. For example, for the model named `User` you will get the following resource:

``` php
<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * 
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $email_verified_at
 * @property string $password
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class UserResource extends JsonResource
{
     /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'remember_token' => $this->remember_token,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

```

## Publish config file

If you want to publish the config file to change the default namespace for the generated resources and the directory where the resources will be generated, you can run the following command:

``` bash
php artisan vendor:publish --provider="Alibori\LaravelApiResourceGenerator\LaravelApiResourceGeneratorServiceProvider" --tag="config"
```
