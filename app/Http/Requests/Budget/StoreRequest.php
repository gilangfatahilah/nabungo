<?php

namespace App\Http\Requests\Budget;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

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
      'category_id' => ['required', 'integer', 'exists:categories,id'],
      'month' => [
        'required',
        'date',
        function ($attribute, $value, $fail) {
          try {
            $date = Carbon::parse($value);
            $userId = $this->user()?->id;

            if (!$userId) {
              $fail('User not authenticated.');
              return;
            }

            $exists = DB::table('budgets')
              ->whereMonth('month', $date->month)
              ->whereYear('month', $date->year)
              ->where('category_id', $this->input('category_id'))
              ->where('user_id', $userId)
              ->exists();

            if ($exists) {
              $fail('Budget for this category and month already exists.');
            }
          } catch (\Exception $e) {
            $fail('Invalid date format.');
          }
        }
      ],
      'amount' => ['required', 'numeric', 'min:0'],
    ];
  }
}
