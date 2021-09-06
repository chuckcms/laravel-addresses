<?php

namespace Chuckcms\Addresses;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Chuckcms\Addresses\Contracts\Address as AddressContract;

class AddressesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->doPublishing();

        $this->registerModelBindings();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/addresses.php',
            'addresses'
        );
    }

    public function doPublishing()
    {
        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen (credit: Spatie)
            return;
        }

        $this->publishes([
            __DIR__.'/../config/addresses.php' => config_path('addresses.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_addresses_tables.php.stub' => $this->getMigrationFileName('create_addresses_tables.php'),
        ], 'migrations');
    }

    public function registerModelBindings()
    {
        $config = $this->app->config['addresses.models'];

        $this->app->bind(AddressContract::class, $config['address']);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @return string
     */
    public function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}