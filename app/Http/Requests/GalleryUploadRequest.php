<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GalleryUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canManageGallery();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => [
                'required',
                'array',
                'max:10' // Maximum 10 images at once
            ],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB max per image
                'dimensions:min_width=300,min_height=200,max_width=4000,max_height=3000'
            ],
            'imageable_type' => [
                'nullable',
                Rule::in(['App\\Models\\Event', 'App\\Models\\Fest'])
            ],
            'imageable_id' => [
                'nullable',
                'integer',
                'required_with:imageable_type'
            ],
            'captions' => [
                'nullable',
                'array'
            ],
            'captions.*' => [
                'nullable',
                'string',
                'max:255'
            ],
            'alt_texts' => [
                'nullable',
                'array'
            ],
            'alt_texts.*' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'images.max' => 'You can upload a maximum of 10 images at once.',
            'images.*.dimensions' => 'Each image must be between 300x200 and 4000x3000 pixels.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'alt_texts.*.regex' => 'Alt text contains invalid characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->captions) {
            $sanitizedCaptions = [];
            foreach ($this->captions as $caption) {
                $sanitizedCaptions[] = strip_tags($caption);
            }
            $this->merge(['captions' => $sanitizedCaptions]);
        }

        if ($this->alt_texts) {
            $sanitizedAltTexts = [];
            foreach ($this->alt_texts as $altText) {
                $sanitizedAltTexts[] = strip_tags($altText);
            }
            $this->merge(['alt_texts' => $sanitizedAltTexts]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate imageable exists if provided
            if ($this->imageable_type && $this->imageable_id) {
                $model = $this->imageable_type;
                if (!class_exists($model) || !$model::find($this->imageable_id)) {
                    $validator->errors()->add('imageable_id', 'The selected item does not exist.');
                }
            }
        });
    }
}
