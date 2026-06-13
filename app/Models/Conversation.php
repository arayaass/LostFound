<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['item_id', 'created_by'];
    public function users() { return $this->belongsToMany(User::class)->withPivot('last_read_at'); }
    public function messages() { return $this->hasMany(Message::class); }
    public function item() { return $this->belongsTo(Item::class); }
}
