<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

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
    public function boot(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

        Filament::getTenant();

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['id','en'])
                ->circular()
                ->flags([
                    'en' => asset('assets/flags/us.svg'),
                    'id' => asset('assets/flags/id.svg'),
                ]);
        });
    }
}