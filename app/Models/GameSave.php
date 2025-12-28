<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSave extends Model
{
    protected $fillable = [
        'user_id',
        'classroom_id',
        'save_data',
    ];

    protected $casts = [
        'save_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
