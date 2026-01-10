<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class FullRentDateRequest implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dateValue = Carbon::parse($value);
        $minDate = Carbon::now()->subMonth();

        if ($dateValue->lessThan($minDate)) {
            $fail('Data w polu :attribute nie może być starsza niż miesiąc wstecz.');
        }
    }
}
