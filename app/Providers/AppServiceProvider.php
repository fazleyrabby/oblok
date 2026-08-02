<?php

namespace App\Providers;

use App\Enums\AlertMetric;
use App\Support\Alerts\MetricSourceRegistry;
use App\Support\Alerts\Sources\DeploymentStatusMetricSource;
use App\Support\Alerts\Sources\IncidentOpenedMetricSource;
use App\Support\Alerts\Sources\QueueBacklogMetricSource;
use App\Support\Alerts\Sources\ServiceHealthMetricSource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MetricSourceRegistry::class, function ($app) {
            $registry = new MetricSourceRegistry;

            $registry->register(AlertMetric::ServiceHealth, ServiceHealthMetricSource::class);
            $registry->register(AlertMetric::QueueBacklog, QueueBacklogMetricSource::class);
            $registry->register(AlertMetric::DeploymentStatus, DeploymentStatusMetricSource::class);
            $registry->register(AlertMetric::IncidentOpened, IncidentOpenedMetricSource::class);

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
