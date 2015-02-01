<?php

namespace Jontyy\ResqueLoner;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Loner
{
    /** @var JobManager */
    private $jobManager;

    /** @var KeyStorage  */
    private $keyStorage;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param JobManager $jobManager
     * @param KeyStorage $keyStorage
     * @param LoggerInterface $logger
     */
    public function __construct(JobManager $jobManager, KeyStorage $keyStorage, LoggerInterface $logger = null)
    {
        $this->jobManager = $jobManager;
        $this->keyStorage = $keyStorage;
        $this->logger = $logger ?: new NullLogger();
    }

    public function afterPerform(\Resque_Job $job)
    {
        $this->removeKeyForJob($job);
    }

    public function onFailure(\Exception $e, \Resque_Job $job)
    {
        $this->removeKeyForJob($job);
    }

    public function afterEnqueue($class, $args, $queue, $id)
    {
        $key = KeyBuilder::build($queue, [
            'class' => $class,
            'args' => [$args]
        ]);

        if(!$key){
            return;
        }

        if($this->keyStorage->exists($key)){
            $this->logger->debug("Loner: {job} already exists, removing.", ['job' => $class, 'id' => $id]);
            $this->jobManager->dequeue($queue, $class, $id);
            return;
        }
        $this->logger->debug("Logger: {job} not in queue, allowing.", ['job' => $class, 'id' => $id]);
        $this->keyStorage->add($key);
    }

    /**
     * @param \Resque_Job $job
     */
    private function removeKeyForJob(\Resque_Job $job)
    {
        $key = KeyBuilder::build($job->queue, $job->payload);
        if ($key && $this->keyStorage->exists($key)) {
            $this->logger->debug("Loner: removing key for {job}", ['job' => $job->payload['class']]);
            $this->keyStorage->remove($key);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @return Loner
     */
    public static function init(LoggerInterface $logger = null)
    {
        $loner = new self(new JobManager(), new ResqueKeyStorage(), $logger);
        \Resque_Event::listen('afterEnqueue', [$loner, 'afterEnqueue']);
        \Resque_Event::listen('afterPerform', [$loner, 'afterPerform']);
        \Resque_Event::listen('onFailure', [$loner, 'onFailure']);
        return $loner;
    }

}
