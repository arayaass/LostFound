<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::factory()->create(['name' => 'Admin TemuKembali', 'email' => 'admin@temukembali.id', 'role' => 'admin']);
        $users = User::factory(5)->create();
        $names = ['Dompet Kulit Cokelat', 'Kunci Motor Honda', 'Tas Laptop Hitam', 'Kucing Abu-abu', 'Kartu Mahasiswa', 'Jam Tangan Silver'];
        $categories = ['wallet', 'keys', 'bag', 'pet', 'document', 'accessory'];
        foreach ($names as $index => $name) {
            \App\Models\Item::create([
                'user_id' => $users[$index % $users->count()]->id,
                'name' => $name,
                'category' => $categories[$index],
                'slug' => \Illuminate\Support\Str::slug($name).'-demo-'.$index,
                'status' => $index % 2 ? 'found' : 'lost',
                'location' => ['Denpasar', 'Kuta', 'Singaraja'][$index % 3],
                'description' => 'Barang ini memiliki ciri khusus dan terakhir terlihat di sekitar lokasi laporan. Hubungi pelapor jika Anda memiliki informasi.',
                'reported_at' => now()->subDays($index),
            ]);
        }
    }
}
