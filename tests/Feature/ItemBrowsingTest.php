<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolved_item_is_hidden_from_home_and_active_search(): void
    {
        $user = User::factory()->create();
        $active = $this->item($user, 'Kunci Aktif', 'keys', false);
        $resolved = $this->item($user, 'Dompet Selesai', 'wallet', true);

        $this->get(route('home'))->assertSee($active->name)->assertDontSee($resolved->name);
        $this->get(route('items.search'))->assertSee($active->name)->assertDontSee($resolved->name);
        $this->get(route('items.resolved'))->assertSee($resolved->name)->assertDontSee($active->name);
    }

    public function test_items_can_be_filtered_by_category(): void
    {
        $user = User::factory()->create();
        $this->item($user, 'Laptop Biru', 'electronics', false);
        $this->item($user, 'Kunci Motor', 'keys', false);

        $this->get(route('items.search', ['category' => 'electronics']))
            ->assertSee('Laptop Biru')
            ->assertDontSee('Kunci Motor');
    }

    private function item(User $user, string $name, string $category, bool $resolved): Item
    {
        return Item::create(['user_id' => $user->id, 'name' => $name, 'category' => $category, 'slug' => str($name)->slug().'-test', 'status' => 'found', 'location' => 'Denpasar', 'description' => 'Deskripsi barang yang cukup panjang untuk pengujian.', 'reported_at' => now(), 'is_resolved' => $resolved]);
    }
}
