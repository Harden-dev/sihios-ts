<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedFileType implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
        $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        $allowedExtensions = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
        if (!in_array($value->getClientMimeType(), $allowedTypes) && !in_array($value->getClientOriginalExtension(), $allowedExtensions)) {
            $fail('Le fichier doit être un PDF, un document Word ou une image JPEG/PNG.');
        }
        if ($value->getSize() > 10 * 1024 * 1024) {
            $fail('Le fichier doit être inférieur à 10 Mo.');
        }
        
    }
}