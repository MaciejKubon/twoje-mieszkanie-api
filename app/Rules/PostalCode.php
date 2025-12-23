<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PostalCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cleanedValue = str_replace([' ', '-'], '', strtoupper($value));
        $pl_regex = '/^\d{2}-\d{3}$/';

        if (!preg_match($pl_regex, $value)) {
            $fail('Pole :attribute musi być w poprawnym formacie kodu pocztowego (np. 00-000).');
        }
    }
}
