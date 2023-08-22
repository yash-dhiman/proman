<?php

namespace App\Http\Resources\api\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\FileCollection;

class ReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'comment_id'                => $this->resource['comment_id'] ? obfuscate($this->resource['comment_id']) : '',
            'description'               => $this->resource['description'] ? $this->resource['description'] : '',
            'task_id'                   => $this->resource['task_id'] ? obfuscate($this->resource['task_id']) : '',
            'created_by'                => $this->resource['created_by'] ? obfuscate($this->resource['created_by']) : '',
            'created_at'                => $this->resource['created_at'] ? format_date_time($this->resource['created_at']) : '',
            'updated_at'                => $this->resource['updated_at'] ? format_date_time($this->resource['updated_at']) : '',            
            'attachments'               => new FileCollection($this->resource['attachments']),
        ];
    }
}
