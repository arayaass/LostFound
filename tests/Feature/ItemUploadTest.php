<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_image_is_required_and_stored_on_public_disk(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $image = UploadedFile::fake()->createWithContent('dompet.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $response = $this->actingAs($user)->post(route('items.store'), [
            'name' => 'Dompet Hitam',
            'category' => 'wallet',
            'status' => 'lost',
            'location' => 'Denpasar',
            'description' => 'Dompet hitam dengan beberapa kartu penting di dalamnya.',
            'image' => $image,
        ]);

        $response->assertRedirect();
        $item = $user->items()->firstOrFail();
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
    }

    public function test_report_without_image_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('items.store'), [
            'name' => 'Dompet Hitam',
            'category' => 'wallet',
            'status' => 'lost',
            'location' => 'Denpasar',
            'description' => 'Dompet hitam dengan beberapa kartu penting di dalamnya.',
        ])->assertSessionHasErrors('image');
    }
}
