<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            VerifyCsrfToken::class,
            ValidateCsrfToken::class,
        ]);
    }

    public function testIndexDisplaysContacts(): void
    {
        $contacts = Contact::factory()->count(2)->create();

        $response = $this->get(route('contacts.index'));

        $response->assertOk();
        $response->assertSee($contacts[0]->email);
        $response->assertSee($contacts[1]->email);
    }

    public function testCreateDisplaysForm(): void
    {
        $response = $this->get(route('contacts.create'));

        $response->assertOk();
        $response->assertSee('New Contact');
    }

    public function testStoreCreatesContact(): void
    {
        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ];

        $response = $this->post(route('contacts.store'), $payload);

        $contact = Contact::query()->first();

        $this->assertNotNull($contact);
        $this->assertSame('jane@example.com', $contact->email);

        $response->assertRedirect(route('contacts.show', $contact));
        $this->assertDatabaseHas('contacts', ['email' => 'jane@example.com']);
    }

    public function testStoreRequiresEmail(): void
    {
        $response = $this->post(route('contacts.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('contacts', 0);
    }

    public function testShowDisplaysContact(): void
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'Novak',
            'email' => 'alex@example.com',
        ]);

        $response = $this->get(route('contacts.show', $contact));

        $response->assertOk();
        $response->assertSee('Alex');
        $response->assertSee('Novak');
        $response->assertSee('alex@example.com');
    }

    public function testEditDisplaysContact(): void
    {
        $contact = Contact::factory()->create([
            'email' => 'editme@example.com',
        ]);

        $response = $this->get(route('contacts.edit', $contact));

        $response->assertOk();
        $response->assertSee('Edit Contact');
        $response->assertSee('editme@example.com');
    }

    public function testUpdatePersistsChanges(): void
    {
        $contact = Contact::factory()->create([
            'email' => 'old@example.com',
        ]);

        $response = $this->put(route('contacts.update', $contact), [
            'first_name' => 'Lukas',
            'last_name' => 'Svoboda',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('contacts.show', $contact));
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'Lukas',
            'last_name' => 'Svoboda',
            'email' => 'new@example.com',
        ]);
    }

    public function testUpdateRequiresUniqueEmail(): void
    {
        $first = Contact::factory()->create(['email' => 'first@example.com']);
        $second = Contact::factory()->create(['email' => 'second@example.com']);

        $response = $this->put(route('contacts.update', $first), [
            'first_name' => $first->first_name,
            'last_name' => $first->last_name,
            'email' => $second->email,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseHas('contacts', ['email' => 'first@example.com']);
    }

    public function testUpdateAllowsSameEmailForCurrentContact(): void
    {
        $contact = Contact::factory()->create([
            'email' => 'same@example.com',
        ]);

        $response = $this->put(route('contacts.update', $contact), [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => 'same@example.com',
        ]);

        $response->assertRedirect(route('contacts.show', $contact));
        $this->assertDatabaseHas('contacts', ['email' => 'same@example.com']);
    }

    public function testDestroyDeletesContact(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->delete(route('contacts.destroy', $contact));

        $response->assertRedirect(route('contacts.index'));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
