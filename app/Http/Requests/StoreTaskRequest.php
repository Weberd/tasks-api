<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:planned,in_progress,done'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:today'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'attachment' => ['nullable', 'file', 'max:10240'], // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required',
            'status.in' => 'Status must be one of: planned, in_progress, done',
            'completion_date.after_or_equal' => 'Completion date must be today or in the future',
            'assignee_id.exists' => 'Selected assignee does not exist',
            'attachment.max' => 'Attachment size must not exceed 10MB',
        ];
    }

    /**
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}
