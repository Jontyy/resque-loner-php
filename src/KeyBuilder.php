<?php

namespace Jontyy\ResqueLoner;

class KeyBuilder 
{

    /**
     * @param string $queue
     * @param array the resque payload
     * @return string|null
     */
    public static function build($queue, array $payload)
    {
        $class = $payload['class'];

        if(!is_callable([$class, 'getLonerKey'])){
            return;
        }
        if(!$key = call_user_func([$class, 'getLonerKey'], $payload)){
            return;
        }
        $base = "loners:queue:{$queue}:job:";
        $key = is_string($key) ? $key : self::defaultKey($payload);
        return $base.$key;
    }

    /**
     * @param array $payload
     * @return string
     */
    private static function defaultKey($payload)
    {
        $key = array_intersect_key($payload, array_flip(['class', 'args']));
        return md5(serialize($key));
    }

} 