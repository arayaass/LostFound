<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HandoverEvent extends Model
{
    protected $fillable = ['handover_id', 'user_id', 'event', 'description'];
    public function user() { return $this->belongsTo(User::class); }
    public function handover() { return $this->belongsTo(Handover::class); }
}
