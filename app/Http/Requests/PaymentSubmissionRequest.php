<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration_id' => [
                'required',
                'exists:registrations,id'
            ],
            'payment_method' => [
                'required',
                Rule::in(['bkash', 'nagad', 'rocket', 'bank_transfer', 'cash'])
            ],
            'transaction_id' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\-_]+$/' // Alphanumeric, hyphens, underscores only
            ],
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subDays(30)->format('Y-m-d') // Within last 30 days
            ],
            'payment_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:10000'
            ],
            'payment_screenshot' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:1024', // 1MB max
                'dimensions:min_width=200,min_height=200'
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
            'transaction_id.regex' => 'Transaction ID can only contain letters, numbers, hyphens, and underscores.',
            'payment_date.after' => 'Payment date cannot be more than 30 days ago.',
            'payment_screenshot.dimensions' => 'Payment screenshot must be at least 200x200 pixels.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'transaction_id' => strtoupper(strip_tags(trim($this->transaction_id))),
            'notes' => strip_tags($this->notes),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if registration belongs to current user
            $registration = \App\Models\Registration::find($this->registration_id);
            if ($registration && $registration->user_id !== auth()->id()) {
                $validator->errors()->add('registration_id', 'You can only submit payment for your own registrations.');
            }

            // Check if payment is required for this registration
            if ($registration && !$registration->payment_required) {
                $validator->errors()->add('registration_id', 'Payment is not required for this registration.');
            }

            // Check if payment amount matches expected amount
            if ($registration && $registration->payment_amount != $this->payment_amount) {
                $validator->errors()->add('payment_amount', 'Payment amount does not match the required amount.');
            }

            // Check for duplicate transaction ID
            $existingPayment = \App\Models\Registration::where('transaction_id', $this->transaction_id)
                ->where('id', '!=', $this->registration_id)
                ->first();

            if ($existingPayment) {
                $validator->errors()->add('transaction_id', 'This transaction ID has already been used.');
            }
        });
    }
}
