<?php

namespace App\Http\Requests\Debt;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'        => ['required', 'numeric', 'min:1'],
            'payment_date'  => ['required', 'date'],
            'account_id'    => ['nullable', 'exists:accounts,id'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
