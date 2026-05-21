<?php

namespace App\Providers;

use App\Repositories\CategoryRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TagRepository;
use App\Services\WoohooClient;
use App\Services\Woohoo\WoohooOrderPayloadBuilder;
use App\Services\Woohoo\WoohooActivatedCardsService;
use App\Services\Woohoo\WoohooOrderService;
use App\Services\Woohoo\WoohooOrderStatusService;
use App\Services\Woohoo\WoohooRefnoGenerator;
use App\Services\Order\FulfillOrderViaWoohooService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WoohooClient::class, fn () => WoohooClient::fromConfig());
        $this->app->singleton(WoohooRefnoGenerator::class, fn () => WoohooRefnoGenerator::fromConfig());
        $this->app->singleton(WoohooOrderPayloadBuilder::class);
        $this->app->singleton(WoohooActivatedCardsService::class);
        $this->app->singleton(WoohooOrderService::class);
        $this->app->singleton(WoohooOrderStatusService::class);
        $this->app->singleton(FulfillOrderViaWoohooService::class);
        $this->app->singleton(OrderRepository::class);
        $this->app->singleton(OrderItemRepository::class);
        $this->app->singleton(CategoryRepository::class);
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(TagRepository::class);
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');
    }
}
