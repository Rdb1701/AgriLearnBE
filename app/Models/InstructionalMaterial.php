<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class InstructionalMaterial extends Model
{
    /** @use HasFactory<\Database\Factories\InstructionalMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'uploaded_by',
        'title',
        'description',
        'file_type',
        'file_path',
        'isOffline'
    ];


    public function instruction_classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function instructor_classroom()
    {
        return $this->belongsTo(User::class, 'uplaoded_by');
    }
}
