<?php

namespace App\Providers;

use App\Auth\ApiKeyGuard;
use App\Enums\AlertMetric;
use App\Enums\MessagingPlatform;
use App\Services\Messaging\Drivers\SlackDriver;
use App\Services\Messaging\MessagingDriverRegistry;
use App\Support\Alerts\MetricSourceRegistry;
use App\Support\Alerts\Sources\DeploymentStatusMetricSource;
use App\Support\Alerts\Sources\IncidentOpenedMetricSource;
use App\Support\Alerts\Sources\QueueBacklogMetricSource;
use App\Support\Alerts\Sources\ServiceHealthMetricSource;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->app->singleton(MessagingDriverRegistry::class, function ($app) {
            $registry = new MessagingDriverRegistry;

            $registry->register(MessagingPlatform::Slack, SlackDriver::class);

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::extend('api_key', function ($app, $name, array $config) {
            return new ApiKeyGuard(
                $app['auth']->createUserProvider($config['provider'] ?? null),
            );
        });

        RateLimiter::for('api_key', function (Request $request) {
            $token = $request->bearerToken();
            $key = $token
                ? 'api-key:'.hash('sha256', $token)
                : 'ip:'.(string) $request->ip();

            return Limit::perMinute((int) config('oblok.api.rate_limit'))->by($key);
        });
    }
}
