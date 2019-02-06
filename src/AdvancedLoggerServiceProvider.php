<?php

namespace Brackets\AdvancedLogger;

use Brackets\AdvancedLogger\Jobs\RequestLogJob;
use Brackets\AdvancedLogger\Services\Benchmark;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            $this->logRequest($request, $response, $this);
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
        $excludedPaths = config('advanced-logger.request.excluded-paths');
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

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     */
    private function logRequest(Request $request, Response $response): void
    {
        if (!$this->excluded($request)) {
            $task = app(RequestLogJob::class, ['request' => $request, 'response' => $response]);
            if (is_null($queueName = config('advanced-logger.request.queue'))) {
                $task->handle();
            } else {
                $this->dispatch(is_string($queueName) ? $task->onQueue($queueName) : $task);
            }
        }
    }
}