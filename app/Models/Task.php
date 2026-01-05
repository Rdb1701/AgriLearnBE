<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function roomTasks()
    {
        return $this->hasMany(RoomTask::class);
    }
}
