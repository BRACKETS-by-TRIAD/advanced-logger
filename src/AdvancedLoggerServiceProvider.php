<?php

namespace Brackets\AdvancedLogger;

use Brackets\AdvancedLogger\Jobs\LogJob;
use Brackets\AdvancedLogger\Services\Benchmark;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

/**
 * Class AdvancedLoggerServiceProvider
 */
class AdvancedLoggerServiceProvider extends ServiceProvider
{
    use DispatchesJobs;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/advanced-logger.php' => config_path('advanced-logger.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Benchmark::start('application');
        $this->mergeConfigFrom(__DIR__ . '/../config/advanced-logger.php', 'advanced-logger');

        $this->app['events']->listen('kernel.handled', function ($request, $response) {
            Benchmark::end('application');
            if (!$this->excluded($request)) {
                $task = app(LogJob::class, ['request' => $request, 'response' => $response]);
                if (is_null($queueName = config('advanced-logger.queue'))) {
                    $task->handle();
                } else {
                    $this->dispatch(is_string($queueName) ? $task->onQueue($queueName) : $task);
                }
            }
        });
    }

    /**
     * Check if current path is not excluded
     *
     * @param Request $request
     * @return bool
     */
    protected function excluded(Request $request): bool
    {
        $excludedPaths = config('advanced-logger.excluded-paths');
        if (null === $excludedPaths || empty($excludedPaths)) {
            return false;
        }
        foreach ($excludedPaths as $excludedPath) {
            if ($request->is($excludedPath)) {
                return true;
            }
        }
        return false;
    }
}