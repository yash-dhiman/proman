<?php

namespace App\Http\Resources\api\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                    'task_id'                   => $this->resource['task_id'] ? obfuscate($this->resource['task_id']) : '',
                    'title'                     => $this->resource['title'] ? $this->resource['title'] : '',
                    'ticket_id'                 => $this->resource['ticket_id'] ? $this->resource['project']['project_code'] . '-' . $this->resource['ticket_id'] : '',
                    'description'               => $this->resource['description'] ? $this->resource['description'] : '',
                    'project_id'                => $this->resource['project_id'] ? obfuscate($this->resource['project_id']) : '',
                    'tasklist_id'               => $this->resource['tasklist_id'] ? obfuscate($this->resource['tasklist_id']) : '',
                    'start_date'                => $this->resource['start_date'] ? format_date($this->resource['start_date']) : '',
                    'end_date'                  => $this->resource['end_date'] ? format_date($this->resource['end_date']) : '',
                    'stage_id'                  => $this->resource['stage_id'] ? obfuscate($this->resource['stage_id']) : '',
                    'status'                    => $this->resource['status'] ? $this->resource['status'] : '',
                    'assignees'                 => $this->resource['assignees'] ? obfuscate_multiple($this->resource['assignees'], true) : [],
                    'created_by'                => $this->resource['created_by'] ? obfuscate($this->resource['created_by']) : '',
                    'created_at'                => $this->resource['created_at'] ? format_date_time($this->resource['created_at']) : '',
                    'updated_at'                => $this->resource['updated_at'] ? format_date_time($this->resource['updated_at']) : '',
                    'updated_by'                => $this->resource['updated_by'] ? obfuscate($this->resource['updated_by']) : '',
                    'deleted'                   => $this->resource['deleted'] ? true : false,
                    'deleted_by'                => $this->resource['deleted_by'] ? obfuscate($this->resource['deleted_by']) : '',
                ];
    }
}
