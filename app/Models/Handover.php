<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Handover extends Model
{
    protected $fillable = ['item_id', 'claimant_id', 'status', 'claim_note', 'owner_note', 'meeting_location', 'meeting_at', 'owner_confirmed_at', 'claimant_confirmed_at', 'completed_at'];
    protected function casts(): array { return ['meeting_at' => 'datetime', 'owner_confirmed_at' => 'datetime', 'claimant_confirmed_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function item() { return $this->belongsTo(Item::class); }
    public function claimant() { return $this->belongsTo(User::class, 'claimant_id'); }
    public function events() { return $this->hasMany(HandoverEvent::class); }
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'requested' => 'Menunggu Verifikasi',
            'approved' => 'Siap Diserahkan',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
            default => ucfirst($this->status),
        };
    }
}
