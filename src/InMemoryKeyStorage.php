<?php

namespace Jontyy\ResqueLoner;

class InMemoryKeyStorage implements KeyStorage
{
    public  $keys = [];
    /**
     * Check if a key exists
     * @param  string $key the job key
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->keys[$key]) && $this->keys[$key];
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        $this->keys[$key] = null;
    }

    /**
     * @param string $key
     */
    public function add($key)
    {
        $this->keys[$key] = true;
    }
}