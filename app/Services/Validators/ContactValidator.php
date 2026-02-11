<?php

declare(strict_types=1);

namespace App\Services\Validators;

use App\Data\Contact;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

final class ContactValidator
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rulesForStore(): array
    {
        return $this->rulesForRequest(unique: true);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rulesForUpdate(Contact $contact): array
    {
        return $this->rulesForRequest(unique: true, ignore: $contact);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.max' => 'First name may not be greater than 100 characters.',
            'last_name.max' => 'Last name may not be greater than 100 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email may not be greater than 254 characters.',
            'email.unique' => 'Email has already been taken.',
        ];
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    private function rulesForRequest(bool $unique, ?Contact $ignore = null): array
    {
        $rules = $this->rules();
        /**
         * @var array<string> $emailRules
         */
        $emailRules = Arr::pull($rules, 'email');

        if ($unique) {
            $rule = Rule::unique('contacts', 'email');
            if ($ignore !== null) {
                $rule = $rule->ignore($ignore->uuid->toString());
            }
            $emailRules[] = $rule;
        }

        /** @var array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> $result */
        $result = [
            ...$rules,
            'email' => $emailRules,
        ];

        return $result;
    }
}
