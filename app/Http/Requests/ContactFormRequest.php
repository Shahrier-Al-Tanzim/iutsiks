<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Contact form is public
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.]+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'subject' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,!?()]+$/'
            ],
            'message' => [
                'required',
                'string',
                'min:10',
                'max:2000'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/',
                'max:20'
            ],
            'g-recaptcha-response' => [
                'sometimes',
                'required'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and periods.',
            'subject.regex' => 'Subject contains invalid characters.',
            'phone.regex' => 'Please enter a valid phone number.',
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strip_tags(trim($this->name)),
            'email' => strtolower(strip_tags(trim($this->email))),
            'subject' => strip_tags(trim($this->subject)),
            'message' => strip_tags($this->message),
            'phone' => strip_tags(trim($this->phone)),
        ]);
    }
}
