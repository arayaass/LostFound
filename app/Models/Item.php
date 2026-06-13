<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'electronics' => 'Elektronik',
        'keys' => 'Kunci',
        'wallet' => 'Dompet',
        'bag' => 'Tas',
        'document' => 'Dokumen / Kartu',
        'vehicle' => 'Kendaraan',
        'pet' => 'Hewan',
        'accessory' => 'Perhiasan / Aksesori',
        'clothing' => 'Pakaian',
        'other' => 'Lainnya',
    ];

    protected $fillable = ['user_id', 'name', 'category', 'slug', 'status', 'location', 'latitude', 'longitude', 'description', 'image_path', 'firebase_id', 'reported_at', 'is_resolved', 'is_spam'];
    protected function casts(): array { return ['reported_at' => 'datetime', 'is_resolved' => 'boolean', 'is_spam' => 'boolean']; }
    public function user() { return $this->belongsTo(User::class); }
    public function matches() { return $this->belongsToMany(Item::class, 'item_matches', 'item_id', 'matched_item_id')->withPivot('score', 'reason')->withTimestamps(); }
    public function handovers() { return $this->hasMany(Handover::class); }
    public function getImageUrlAttribute(): string { return $this->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path) : 'https://placehold.co/800x600/e8efff/2563eb?text='.urlencode($this->name); }
    public function getStatusLabelAttribute(): string { return $this->status === 'lost' ? 'Hilang' : 'Ditemukan'; }
    public function getCategoryLabelAttribute(): string { return self::CATEGORIES[$this->category] ?? self::CATEGORIES['other']; }
    public function scopeActive($query) { return $query->where('is_resolved', false)->where('is_spam', false); }
    public function scopeResolved($query) { return $query->where('is_resolved', true)->where('is_spam', false); }
}
