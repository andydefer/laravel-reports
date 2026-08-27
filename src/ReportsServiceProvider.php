<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports;

use AndyDefer\LaravelReports\Contracts\Repositories\ReportRepositoryInterface;
use AndyDefer\LaravelReports\Contracts\Services\ReportServiceInterface;
use AndyDefer\LaravelReports\Repositories\ReportRepository;
use AndyDefer\LaravelReports\Services\ReportService;
use Illuminate\Support\ServiceProvider;

final class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register concrete classes
        $this->app->singleton(ReportRepository::class, function ($app) {
            return new ReportRepository;
        });

        $this->app->singleton(ReportService::class, function ($app) {
            return new ReportService(
                $app->make(ReportRepositoryInterface::class)
            );
        });

        // Bind interfaces to concrete classes
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
        $this->app->bind(ReportServiceInterface::class, ReportService::class);
    }

    public function boot(): void
    {
        // Migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Publishes
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'reports-migrations');
    }
}
