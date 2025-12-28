<?php

namespace App\Rules;

use App\Models\Objects;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ObjectInRentAssigment implements ValidationRule
{
    /**
     * @param  string  $attribute  Nazwa pola (id_object)
     * @param  mixed  $value      Wartość przesłana (ID obiektu)
     * @param  \Closure  $fail     Funkcja wywoływana przy błędzie
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Objects::where('id', $value)
            ->where('id_owner', auth()->id())
            ->where('deleted_at', null)
            ->exists();

        if (!$exists) {
            $fail('Wybrany obiekt jest nieprawidłowy lub nie należy do Ciebie.');
        }
    }
}
