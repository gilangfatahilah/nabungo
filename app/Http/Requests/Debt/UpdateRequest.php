<?php

namespace App\Http\Requests\Debt;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:debt,receivable'],
            'amount'        => ['required', 'numeric', 'min:1'],
            'contact_name'  => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'due_date'      => ['nullable', 'date'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
