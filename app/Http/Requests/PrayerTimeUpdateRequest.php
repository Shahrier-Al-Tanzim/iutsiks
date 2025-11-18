<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrayerTimeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canManagePrayerTimes();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . now()->addYear()->format('Y-m-d')
            ],
            'fajr' => [
                'required',
                'date_format:H:i',
                'before:dhuhr'
            ],
            'dhuhr' => [
                'required',
                'date_format:H:i',
                'after:fajr',
                'before:asr'
            ],
            'asr' => [
                'required',
                'date_format:H:i',
                'after:dhuhr',
                'before:maghrib'
            ],
            'maghrib' => [
                'required',
                'date_format:H:i',
                'after:asr',
                'before:isha'
            ],
            'isha' => [
                'required',
                'date_format:H:i',
                'after:maghrib'
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'fajr.before' => 'Fajr time must be before Dhuhr time.',
            'dhuhr.after' => 'Dhuhr time must be after Fajr time.',
            'dhuhr.before' => 'Dhuhr time must be before Asr time.',
            'asr.after' => 'Asr time must be after Dhuhr time.',
            'asr.before' => 'Asr time must be before Maghrib time.',
            'maghrib.after' => 'Maghrib time must be after Asr time.',
            'maghrib.before' => 'Maghrib time must be before Isha time.',
            'isha.after' => 'Isha time must be after Maghrib time.',
            'location.regex' => 'Location contains invalid characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'location' => strip_tags($this->location),
            'notes' => strip_tags($this->notes),
        ]);
    }
}
