<?php

namespace App\Http\Resources\api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                    'user_id'           => $this->resource['user_id'] ? obfuscate($this->resource['user_id']) : '',
                    'email'             => $this->resource['email'] ? $this->resource['email'] : '',
                    'first_name'        => $this->resource['first_name'] ? $this->resource['first_name'] : '',
                    'last_name'         => $this->resource['last_name'] ? $this->resource['last_name'] : '',
                    'last_active'       => $this->resource['last_active'] ? format_date_time($this->resource['last_active']) : '',
                    'active'            => $this->resource['active'] ?  true :  false,
                    'suspended'         => $this->resource['suspended'] ?  true :  false,
                    'profile_picture'   => $this->userProfilePic(),
                    'created_at'        => $this->resource['created_at'] ? format_date_time($this->resource['created_at']) : '',
                ];
    }

    private function userProfilePic()
    {
        return $this->resource['profile_pic'];
    }
}
