<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiSupportChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:4000'],
            'conversation_id' => ['nullable', 'integer', 'min:1'],
            'locale' => ['nullable', 'string', 'in:en,sw'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Please enter a message.',
            'message.max' => 'Message is too long (max 4000 characters).',
        ];
    }
}
