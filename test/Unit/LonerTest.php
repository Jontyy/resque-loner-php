<?php

namespace Jontyy\ResqueLoner\Test\Unit;

use Jontyy\ResqueLoner\InMemoryKeyStorage;
use Jontyy\ResqueLoner\JobManager;
use Jontyy\ResqueLoner\KeyBuilder;
use Jontyy\ResqueLoner\Loner;

class TestJob
{
    public static function getLonerKey()
    {
        return 'TestJob';
    }
}

class NonLonerJob
{
}

class LonerTest extends \PHPUnit_Framework_TestCase
{
    /** @var InMemoryKeyStorage */
    private $keyStorage;

    /** @var JobManager|\PHPUnit_Framework_MockObject_MockObject */
    private $jobManager;

    /** @var Loner */
    private $loner;

    public function setUp()
    {
        $this->keyStorage = new InMemoryKeyStorage();
        $this->jobManager = $this->getMock('Jontyy\\ResqueLoner\\JobManager');
        $this->loner = new Loner($this->jobManager, $this->keyStorage);
    }

    public function testAddsKeyIfJobHasGetLonerKeyMethod()
    {
        $this->loner->afterEnqueue(TestJob::class, [], 'default', 123);
        $key = $this->createKey('default', TestJob::class, 123);
        $this->assertTrue($this->keyStorage->exists($key));
    }

    public function testSkipsJobsWithoutGetLonerKeyMethod()
    {
        $this->loner->afterEnqueue(__CLASS__,[],'default', '123');
        $this->assertCount(0, $this->keyStorage->keys, 'No key should have been added');
    }

    public function testDequeuesJobIfOneIsAlreadyQueued()
    {
        $this->loner->afterEnqueue(TestJob::class, [], 'default', 123);
        $key = $this->createKey('default', TestJob::class, 123);
        $this->assertTrue($this->keyStorage->exists($key));

        $this->jobManager->expects($this->once())
            ->method('dequeue')
            ->with('default', TestJob::class, 123);

        $this->loner->afterEnqueue(TestJob::class, [], 'default', 123);
    }

    public function testRemovesKeyAfterAJobHasCompleted()
    {
        $key = $this->createKey('test_queue', TestJob::class, []);
        $this->keyStorage->add($key);
        $this->loner->afterPerform($this->createResqueJob());
        $this->assertFalse($this->keyStorage->exists($key));
    }

    public function testRemovesKeyOnFailure()
    {
        $key = $this->createKey('test_queue', TestJob::class, []);
        $this->keyStorage->add($key);
        $this->loner->onFailure(new \Exception(), $this->createResqueJob());
        $this->assertFalse($this->keyStorage->exists($key));
    }

    private function createResqueJob()
    {
        return new \Resque_Job('test_queue', [
            'class' => TestJob::class,
            'args' => []
        ]);
    }

    /**
     * @param string $queue
     * @param string $job
     * @param mixed $args
     * @return null|string
     */
    private function createKey($queue, $job, $args)
    {
        return KeyBuilder::build($queue,[
            'class' => $job,
            'args' => [$args]
        ]);
    }
}
 