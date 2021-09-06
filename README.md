# Laravel Addresses

An easy way to manage addresses for Eloquent models in Laravel.

## Installation

Require the package by running
``` composer require chuckcms/addresses```

## Publish configuration and migration
``` php artisan vendor:publish --provider="Chuckcms\Addresses\AddressesServiceProvider" ```

This command will publish a ```config/addresses.php``` and a migration file.

> You can modify the default fields and their rules by changing both of these files.

After publishing you can run the migrations

``` php artisan migrate ```

## Usage

You can use the ```HasAddresses``` trait on any model.

```php
<?php

namespace App\Models;

use Chuckcms\Addresses\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasAddresses;

    // ...
} 
```

After doing this you can use the following methods.

#### Add an address to a model

```php
$post = Post::first();
$post->addAddress([
	'label' 				=> 'My address', // required
	'street' 				=> 'Main Street', // defaults to: null
	'housenumber' 				=> '100', // defaults to: null
	'housenumber_postfix' 			=> 'B1', // defaults to: null
	'postal_code'				=> '10001', // defaults to: null
	'city'					=> 'Chuck City', // defaults to: null
	'state'					=> 'New Chuck State', // defaults to: null
	'country'				=> 'Chuckland', // defaults to: null
	'latitude'				=> 51.13128, // defaults to: null
	'longitude'				=> 4.57041, // defaults to: null
	'is_primary'				=> true, // defaults to: false
	'is_billing'				=> false, // defaults to: false
	'is_shipping'				=> false, // defaults to: false
]);
```

#### Update an existing address

```php
$post = Post::first();
$address = $post->getPrimaryAddress();

$post->updateAddress($address, ['label' => 'My new address']);
```

#### Delete an address from a model

```php
$post = Post::first();
$address = $post->addresses()->first();

if ($post->deleteAddress($address)) {
	//do something
}
```

#### Determine if a model has any addresses

```php
$post = Post::first();

if ($post->hasAddresses()) {
	//do something
}
```