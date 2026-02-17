<?php

namespace App\Providers;

use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TagRepository;
use App\Services\WoohooClient;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WoohooClient::class, fn () => WoohooClient::fromConfig());
        $this->app->singleton(CategoryRepository::class);
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(TagRepository::class);
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');
    }
}
