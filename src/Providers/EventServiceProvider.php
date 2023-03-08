<?php

namespace Brackets\AdvancedLogger\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected array $listen = [

    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected array $subscribe = [
        \Brackets\AdvancedLogger\Listeners\RequestLoggerListener::class,
    ];
}
