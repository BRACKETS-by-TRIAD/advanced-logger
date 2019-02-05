<?php

namespace Brackets\AdvancedLogger\Services;

use Exception;

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
     * @param $name
     * @return mixed
     */
    public static function start($name)
    {
        $start = microtime(true);
        static::$timers[$name] = [
            'start' => $start
        ];
        return $start;
    }

    /**
     * @param $name
     * @return float
     * @throws Exception
     */
    public static function end($name): float
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
        throw new Exception("Benchmark '{$name}' not started");
    }

    /**
     * @param $name
     * @return float
     * @throws Exception
     */
    public static function duration($name): float
    {
        return static::end($name);
    }
}