<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class FilesystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Filesystem::class, function () {
            return new Filesystem;
        });

        $this->app->alias(Filesystem::class, 'files');
    }
}
