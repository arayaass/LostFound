<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('category', 40)->default('other')->index()->after('name');
        });

        DB::table('items')->orderBy('id')->each(function ($item) {
            $name = strtolower($item->name);
            $category = match (true) {
                str_contains($name, 'kunci') => 'keys',
                str_contains($name, 'dompet') => 'wallet',
                str_contains($name, 'tas') => 'bag',
                str_contains($name, 'kartu'), str_contains($name, 'dokumen'), str_contains($name, 'ktp'), str_contains($name, 'sim') => 'document',
                str_contains($name, 'laptop'), str_contains($name, 'hp'), str_contains($name, 'ponsel'), str_contains($name, 'handphone'), str_contains($name, 'kamera'), str_contains($name, 'earphone') => 'electronics',
                str_contains($name, 'kucing'), str_contains($name, 'anjing'), str_contains($name, 'hewan') => 'pet',
                str_contains($name, 'jam'), str_contains($name, 'cincin'), str_contains($name, 'gelang'), str_contains($name, 'kalung') => 'accessory',
                str_contains($name, 'motor'), str_contains($name, 'mobil'), str_contains($name, 'sepeda') => 'vehicle',
                str_contains($name, 'baju'), str_contains($name, 'jaket'), str_contains($name, 'sepatu') => 'clothing',
                default => 'other',
            };
            DB::table('items')->where('id', $item->id)->update(['category' => $category]);
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
