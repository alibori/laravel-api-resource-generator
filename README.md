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
 * @property $id
 * @property $name
 * @property $email
 * @property $email_verified_at
 * @property $password
 * @property $remember_token
 * @property $created_at
 * @property $updated_at
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
