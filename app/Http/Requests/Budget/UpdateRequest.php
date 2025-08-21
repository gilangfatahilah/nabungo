<?php

namespace App\Http\Requests\Budget;

use Illuminate\Validation\Rule;

class UpdateRequest extends StoreRequest
{
  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $rules = parent::rules();

    $budget = $this->route('budget');

    $rules['month'] = [
      'required',
      'date',
      Rule::unique('budgets')
        ->ignore($budget->id)
        ->where(
          fn($query) => $query
            ->where('category_id', $this->input('category_id'))
            ->where('user_id', $this->user()->id)
        ),
    ];

    return $rules;
  }
}
