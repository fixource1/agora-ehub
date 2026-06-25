<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\AudienceLevel;
use App\Enums\ResourceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Resource::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'resource_type_id' => ['required', 'exists:resource_types,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'language' => ['nullable', 'string', 'max:10'],
            'audience_level' => ['nullable', Rule::enum(AudienceLevel::class)],
            'status' => ['nullable', Rule::enum(ResourceStatus::class)],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['string', 'max:50'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
