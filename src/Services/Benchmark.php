<?php

namespace Brackets\AdvancedLogger\Services;

use RuntimeException;

/**
 * Class Benchmark
 */
class Benchmark
{
    /**
     * @var array
     */
    protected static $timers = [];

    /**
     * @param string $name
     * @return mixed
     */
    public static function start(string $name)
    {
        $start = microtime(true);
        static::$timers[$name] = [
            'hash' => sha1(time()),
            'start' => $start,
        ];
        return $start;
    }

    /**
     * @param string $name
     * @return float
     */
    public static function end(string $name): float
    {
        $end = microtime(true);
        if (isset(static::$timers[$name]) && isset(static::$timers[$name]['start'])) {
            if (isset(static::$timers[$name]['duration'])) {
                return static::$timers[$name]['duration'];
            }
            $start = static::$timers[$name]['start'];
            static::$timers[$name]['end'] = $end;
            static::$timers[$name]['duration'] = $end - $start;
            return static::$timers[$name]['duration'];
        }
        throw new RuntimeException("Benchmark '{$name}' not started");
    }

    /**
     * @param string $name
     * @return float
     */
    public static function duration(string $name): float
    {
        return static::end($name);
    }

    /**
     * @param string $name
     * @return string
     */
    public static function hash(string $name): string
    {
        if (isset(static::$timers[$name]) && isset(static::$timers[$name]['start'])) {
            return static::$timers[$name]['hash'];
        }
        throw new RuntimeException("Benchmark '{$name}' not started");
    }
}