<?php

namespace App\Http\Requests;

use App\Models\MetricSample;
use Illuminate\Foundation\Http\FormRequest;

class IngestMetricsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project && $this->user()?->can('create', [MetricSample::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'metrics' => ['required', 'array', 'min:1', 'max:1000'],
            'metrics.*.name' => ['required', 'string', 'max:255'],
            'metrics.*.value' => ['required', 'numeric'],
            'metrics.*.labels' => ['nullable', 'array'],
            'metrics.*.labels.*' => ['string'],
            'metrics.*.timestamp' => ['nullable', 'date'],
        ];
    }
}
