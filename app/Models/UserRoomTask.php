<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoomTask extends Model
{
    protected $fillable = [
        'user_id',
        'room_task_id',
        'score',
        'is_completed',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roomTask()
    {
        return $this->belongsTo(RoomTask::class);
    }
}
