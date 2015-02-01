<?php

namespace Jontyy\ResqueLoner;

class ResqueKeyStorage implements KeyStorage
{

    /**
     * Check if a key exists
     * @param  string $key the job key
     * @return bool
     */
    public function exists($key)
    {
        return \Resque::redis()->exists($key);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        \Resque::redis()->del($key);
    }

    /**
     * @param string $key
     */
    public function add($key)
    {
        \Resque::redis()->set($key, 1);
    }
}