<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\Contact as ContactData;
use App\Data\Intent\Contact\UpdateContactIntent;
use App\Services\Validators\ContactValidator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        if (is_string($email)) {
            $this->merge([
                'email' => strtolower(trim($email)),
            ]);
        }
    }

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
        $contact = $this->resolveContact();

        return app(ContactValidator::class)->rulesForUpdate($contact);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return app(ContactValidator::class)->messages();
    }

    public function toIntent(): UpdateContactIntent
    {
        $data = $this->validated();
        /** @var array{email: string, first_name?: string|null, last_name?: string|null} $data */
        $contact = $this->resolveContact();

        return new UpdateContactIntent(
            $contact->uuid,
            $data['email'],
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
        );
    }

    private function resolveContact(): ContactData
    {
        $routeContact = $this->route('contact');
        if ($routeContact instanceof ContactData) {
            return $routeContact;
        }

        abort(404);
    }
}
