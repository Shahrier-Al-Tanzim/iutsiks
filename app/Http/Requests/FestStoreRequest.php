<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canManageEvents();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,!?()]+$/'
            ],
            'description' => [
                'required',
                'string',
                'min:50',
                'max:10000'
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date'
            ],
            'banner_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:3072', // 3MB max for banners
                'dimensions:min_width=300,min_height=200,max_width=2000,max_height=1500'
            ],
            'status' => [
                'sometimes',
                Rule::in(['draft', 'published', 'completed'])
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.regex' => 'The title contains invalid characters.',
            'description.min' => 'The description must be at least 50 characters.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'banner_image.dimensions' => 'The banner image must be between 300x200 and 2000x1500 pixels.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'description' => strip_tags($this->description, '<p><br><strong><em><ul><ol><li><h3><h4>'),
        ]);
    }
}
