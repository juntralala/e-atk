<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_fill(): void
    {
        $item = new Item();
        $item = $item->fill([
            'spesification_name' => 'Sidu'
        ]);

        $this->assertArrayHasKey('specification_name', $item->getAttributes());
        $this->assertArrayNotHasKey('spesification_name', $item->getAttributes());
    }

    public function test_create(): void
    {
        Unit::create(['name' => "Pcs"]);
        $item = Item::create([ // method create() secara internal manggil fill()
            'name' => 'Buku Tulis',
            'spesification_name' => 'Sidu',
            'unit_id' => Unit::firstOrFail()->id,
            'stock' => 0,
            'price' => 0,
        ]);

        $this->assertArrayHasKey('specification_name', $item->getAttributes());
        $this->assertArrayNotHasKey('spesification_name', $item->getAttributes());
    }
}
