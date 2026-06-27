<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
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
            'can_manage_resources' => $this->can('manage resources'),
        ];
    }
}
