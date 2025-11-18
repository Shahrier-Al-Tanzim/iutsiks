<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
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
        $rules = [
            'event_id' => [
                'required',
                'exists:events,id'
            ],
            'registration_type' => [
                'required',
                Rule::in(['individual', 'team'])
            ],
        ];

        // Individual registration rules
        if ($this->registration_type === 'individual') {
            $rules['individual_name'] = [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.]+$/'
            ];
        }

        // Team registration rules
        if ($this->registration_type === 'team') {
            $rules['team_name'] = [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_]+$/'
            ];
            $rules['team_members'] = [
                'required',
                'array',
                'min:2',
                'max:10'
            ];
            $rules['team_members.*'] = [
                'required',
                'exists:users,id',
                'different:' . auth()->id() // Team members can't include the leader
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'individual_name.regex' => 'The name can only contain letters, spaces, hyphens, and periods.',
            'team_name.regex' => 'The team name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'team_members.min' => 'A team must have at least 2 members.',
            'team_members.max' => 'A team cannot have more than 10 members.',
            'team_members.*.different' => 'Team members cannot include the team leader.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->individual_name) {
            $this->merge([
                'individual_name' => strip_tags(trim($this->individual_name))
            ]);
        }

        if ($this->team_name) {
            $this->merge([
                'team_name' => strip_tags(trim($this->team_name))
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if user is already registered for this event
            $existingRegistration = \App\Models\Registration::where('event_id', $this->event_id)
                ->where('user_id', auth()->id())
                ->first();

            if ($existingRegistration) {
                $validator->errors()->add('event_id', 'You are already registered for this event.');
            }

            // Check event capacity
            $event = \App\Models\Event::find($this->event_id);
            if ($event && $event->max_participants) {
                $currentRegistrations = $event->registrations()->where('status', 'approved')->count();
                if ($currentRegistrations >= $event->max_participants) {
                    $validator->errors()->add('event_id', 'This event has reached its maximum capacity.');
                }
            }

            // Check registration deadline
            if ($event && $event->registration_deadline && now() > $event->registration_deadline) {
                $validator->errors()->add('event_id', 'Registration deadline has passed for this event.');
            }
        });
    }
}
