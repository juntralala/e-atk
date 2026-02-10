<?php

namespace Tests\Unit;

use Exception;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LaravelCollectionTest extends TestCase
{
    #[Test]
    function createFromAssoc()
    {
        $collection = collect(['name' => 'ujun', 'age' => 200]);
        $this->assertEquals("ujun", $collection->get('name'));
        $this->assertEquals(200, $collection->get('age'));
    }

    #[Test]
    function interate()
    {
        collect([0, 5, 10])
            ->each(function ($value, $key) {
                $this->assertEquals($key * 5, $value);
            });
    }
}