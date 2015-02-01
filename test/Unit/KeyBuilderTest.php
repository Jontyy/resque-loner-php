<?php

namespace Jontyy\ResqueLoner\Test\Unit;

use Jontyy\ResqueLoner\KeyBuilder;

class FakeJob
{
    public static $response;

    public static function getLonerKey($payload)
    {
        if(is_callable(self::$response)){
            return call_user_func(self::$response, $payload);
        }
        return self::$response;
    }
}


class KeyBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultKey()
    {
        FakeJob::$response = true;
        $payload = ['class' => FakeJob::class, 'args' => [['hello' => 'world']]];

        $expectedKey = 'loners:queue:default:job:'.md5(serialize($payload));
        $key = KeyBuilder::build('default', $payload);
        $this->assertEquals($expectedKey, $key);
    }

    public function testDisabled()
    {
        FakeJob::$response = null;
        $this->assertNull(KeyBuilder::build('default', ['class' => FakeJob::class]));
    }

    public function testCustom()
    {
        FakeJob::$response = function($payload) {
            return md5($payload['class']);
        };
        $expectedKey = 'loners:queue:default:job:'.md5(FakeJob::class);
        $key = KeyBuilder::build('default', [
            'class' => FakeJob::class,
            'args' => [[]]
        ]);
        $this->assertEquals($expectedKey, $key);
    }
}
 