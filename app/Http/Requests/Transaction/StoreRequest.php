<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'category_id' => ['required_if:type,income,expense', 'integer', 'exists:categories,id'],
      'account_id' => ['required', 'integer', 'exists:accounts,id'],
      'account_target_id' => [
        'nullable',
        'required_if:type,transfer',
        'different:account_id',
        'exists:accounts,id',
      ],
      'type' => ['required', 'in:income,expense,transfer'],
      'amount' => ['required', 'numeric', 'min:0'],
      'description' => ['nullable', 'string'],
      'transaction_date' => ['required', 'date']
    ];
  }
}
