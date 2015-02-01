<?php

namespace Jontyy\ResqueLoner;

class JobManager 
{
    /**
     * Dequeue a job
     * @param string $queue
     * @param string $class
     * @param string $id
     */
    public function dequeue($queue, $class, $id)
    {
        \Resque::dequeue($queue, [$class => $id]);
    }
} 