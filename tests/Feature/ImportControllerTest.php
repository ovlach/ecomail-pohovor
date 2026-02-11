<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessImport;
use App\Models\ContactImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreQueuesImportAndRedirects(): void
    {
        Storage::fake('local');
        Queue::fake();
        $this->withoutMiddleware();

        $file = UploadedFile::fake()->create(
            'contacts.xml',
            10,
            'text/xml',
        );

        $response = $this->post(route('import.store'), [
            'file' => $file,
        ]);

        $response
            ->assertRedirect(route('import.index'))
            ->assertSessionHas('importUuid')
            ->assertSessionHas('status', 'Import queued.');

        Queue::assertPushed(ProcessImport::class);
        $this->assertDatabaseCount('contact_imports', 1);
    }

    public function testShowReturnsImport(): void
    {
        $contactImport = ContactImport::factory()->create();

        $response = $this->getJson(route('api.imports.show', $contactImport->id));

        $response
            ->assertOk()
            ->assertJson([
                'id' => $contactImport->id,
                'state' => $contactImport->state->value,
            ])
            ->assertJsonStructure([
                'id',
                'queue_at',
                'started_at',
                'finished_at',
                'total_processed',
                'errors',
                'duplicates',
                'state',
            ]);
    }

    public function testShowReturns404ForInvalidUuid(): void
    {
        $this->getJson('/api/imports/not-a-uuid')
            ->assertNotFound();
    }

    public function testShowReturns404ForMissingImport(): void
    {
        $this->getJson(route('api.imports.show', Uuid::uuid7()->toString()))
            ->assertNotFound();
    }
}
