<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'institution' => $this->institution,
            'department' => $this->department,
            'bio' => $this->bio,
            'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
            'role' => $this->getRoleNames()->first(),
        ];
    }
}
