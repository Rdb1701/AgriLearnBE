<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTask extends Model
{
    protected $fillable = [
        'room_id',
        'task_id',
        'amount',
        'reward',
        'is_active',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'room_id');
    }

    public function userRoomTasks()
    {
        return $this->hasMany(UserRoomTask::class);
    }
}
