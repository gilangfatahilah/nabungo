<?php

namespace App\Http\Requests\Goal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
      'title'         => ['required', 'string', 'max:255'],
      'target_amount' => ['required', 'numeric', 'min:1'],
      'due_date'      => ['nullable', 'date', 'after_or_equal:today'],
      'notes'         => ['nullable', 'string'],
      'status'        => ['required', 'in:ongoing,cancelled,achieved'],
    ];
  }
}
