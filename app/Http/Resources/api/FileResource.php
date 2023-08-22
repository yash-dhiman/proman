<?php

namespace App\Http\Resources\api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                    'file_id'                   => $this->resource['file_id'] ? obfuscate($this->resource['file_id']) : '',
                    'file_name'                 => $this->resource['file_name'] ? $this->resource['file_name'] : '',
                    'file_type'                 => $this->resource['file_type'] ? $this->resource['file_type'] : '',
                    'project_id'                => $this->resource['project_id'] ? obfuscate($this->resource['project_id']) : '',
                    'related_to_id'             => $this->resource['related_to_id'] ? obfuscate($this->resource['related_to_id']) : '',
                    'file_version'              => $this->resource['file_version'] ? $this->resource['file_version'] : '',
                    'file_size'                 => $this->resource['file_size'] ? $this->resource['file_size'] : '',
                    'file_extension'            => $this->resource['file_extension'] ? $this->resource['file_extension'] : '',
                    'pages'                     => $this->resource['pages'] ? $this->resource['pages'] : '',
                    'created_by'                => $this->resource['created_by'] ? obfuscate($this->resource['created_by']) : '',
                    'created_at'                => $this->resource['created_at'] ? format_date_time($this->resource['created_at']) : '',
                    'updated_at'                => $this->resource['updated_at'] ? format_date_time($this->resource['updated_at']) : '',
                    'updated_by'                => $this->resource['updated_by'] ? obfuscate($this->resource['updated_by']) : '',
                    'deleted'                   => $this->resource['deleted'] ? true : false,
                    'deleted_by'                => $this->resource['deleted_by'] ? obfuscate($this->resource['deleted_by']) : '',
                ];
    }
}
