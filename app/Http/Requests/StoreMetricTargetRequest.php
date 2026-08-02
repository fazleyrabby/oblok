<?php

namespace App\Http\Requests;

use App\Models\MetricTarget;
use Illuminate\Foundation\Http\FormRequest;

class StoreMetricTargetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('create', [MetricTarget::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:500'],
        ];
    }
}
