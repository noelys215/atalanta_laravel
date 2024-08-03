<?php

namespace App\Providers;

use App\Livewire\ToggleIsAdmin;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Livewire::component('toggle-is-admin', ToggleIsAdmin::class);
    }
}
