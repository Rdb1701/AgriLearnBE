<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'instructor_id'=> $this->instructor_id,
            'class_name'   => $this->class_name,
            'subject'      => $this->subject,
            'section_code' => $this->section_code
        ];
    }
}
