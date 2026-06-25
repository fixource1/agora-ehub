<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;

class ResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'contributor']);
    }

    public function view(User $user, Resource $resource): bool
    {
        if ($user->can('manage resources')) {
            return true;
        }

        return $user->can('edit own resources') && $resource->uploader_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('upload resources');
    }

    public function update(User $user, Resource $resource): bool
    {
        if ($user->can('manage resources')) {
            return true;
        }

        return $user->can('edit own resources') && $resource->uploader_id === $user->id;
    }

    public function delete(User $user, Resource $resource): bool
    {
        if ($user->can('manage resources')) {
            return true;
        }

        return $user->can('edit own resources')
            && $resource->uploader_id === $user->id
            && in_array($resource->status, ['draft', 'pending_review'], true);
    }
}
