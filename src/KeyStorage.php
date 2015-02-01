<?php

namespace Jontyy\ResqueLoner;

interface KeyStorage 
{
    /**
     * Check if a key exists
     * @param  string $key the job key
     * @return bool
     */
    public function exists($key);

    /**
     * @param string $key
     */
    public function remove($key);

    /**
     * @param string $key
     */
    public function add($key);
} 