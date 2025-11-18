<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventStoreRequest extends FormRequest
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
                'regex:/^[a-zA-Z0-9\s\-_.,!?()]+$/' // Prevent XSS characters
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000'
            ],
            'fest_id' => [
                'nullable',
                'exists:fests,id'
            ],
            'type' => [
                'required',
                Rule::in(['quiz', 'lecture', 'donation', 'competition', 'workshop'])
            ],
            'registration_type' => [
                'required',
                Rule::in(['individual', 'team', 'both', 'on_spot'])
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/'
            ],
            'event_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'event_time' => [
                'required',
                'date_format:H:i'
            ],
            'max_participants' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000'
            ],
            'fee_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000'
            ],
            'registration_deadline' => [
                'nullable',
                'date',
                'after_or_equal:today',
                'before_or_equal:event_date'
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048', // 2MB max
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
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
            'location.regex' => 'The location contains invalid characters.',
            'image.dimensions' => 'The image must be between 100x100 and 2000x2000 pixels.',
            'registration_deadline.before_or_equal' => 'Registration deadline must be before or on the event date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input data
        $this->merge([
            'title' => strip_tags($this->title),
            'description' => strip_tags($this->description, '<p><br><strong><em><ul><ol><li>'),
            'location' => strip_tags($this->location),
        ]);
    }
}
