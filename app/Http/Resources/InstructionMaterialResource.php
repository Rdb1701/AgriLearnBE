<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionMaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'classroom_id' => $this->classroom_id,
            'uploaded_by'  => $this->uploaded_by,
            'title'        => $this->title,
            'uploader_name' => optional($this->instructor_classroom)->name,
            'description'  => $this->description,
            'file_type'    => $this->file_type,
            'file_path'    => $this->file_path,
            'isOffline'    => $this->isOffline,
             'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
