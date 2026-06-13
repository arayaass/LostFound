<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_data_table_and_export_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create();
        Item::create([
            'user_id' => $reporter->id,
            'name' => 'Dompet Uji',
            'slug' => Str::slug('Dompet Uji').'-test',
            'status' => 'lost',
            'location' => 'Denpasar',
            'description' => 'Deskripsi barang uji untuk memastikan fitur ekspor berjalan.',
            'reported_at' => now(),
        ]);

        $this->actingAs($admin)->get('/admin?q=Dompet')->assertOk()->assertSee('Data Tables Laporan');
        $this->actingAs($admin)->get('/admin/export/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($admin)->get('/admin/export/excel')->assertOk()->assertDownload();
        $this->actingAs($admin)->get('/admin/export/csv')->assertOk()->assertDownload();
    }

    public function test_regular_user_cannot_export_admin_data(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/export/pdf')->assertForbidden();
    }
}
