<?php

declare(strict_types=1);

namespace App\Services\ProcessImportMonitor;

use App\Enums\ContactImportStateEnum;
use App\Models\ContactImport;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

class DatabaseProcessImportState implements ProcessImportIdentifiable, ProcessImportState
{
    public function __construct(
        public readonly UuidInterface $uuid,
        string $file
    ) {
        ContactImport::query()->create(
            [
                'id' => $this->uuid()->toString(),
                'state' => ContactImportStateEnum::Pending,
                'file' => $file,
                'queue_at' => Carbon::now(),
            ],
        );
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function start(): void
    {
        ContactImport::query()->where(
            [
                'id' => $this->uuid()->toString(),
            ]
        )->update(
            [
                'started_at' => now(),
                'state' => ContactImportStateEnum::Running,
            ],
        );
    }

    public function fail(): void
    {
        ContactImport::query()->where(
            [
                'id' => $this->uuid()->toString(),
            ],
        )->update(
            [
                'finished_at' => now(),
                'state' => ContactImportStateEnum::Fail,
            ]
        );
    }

    public function finish(): void
    {
        ContactImport::query()->where(
            [
                'id' => $this->uuid()->toString(),
            ],
        )->update(
            [
                'finished_at' => now(),
                'state' => ContactImportStateEnum::Done,
            ]
        );
    }

    public function contactDuplicate(int $processed = 1): void
    {
        ContactImport::query()->where(
            [
                'id' => $this->uuid()->toString(),
            ],
        )->incrementEach(
            [
                'total_processed' => $processed,
                'duplicates' => $processed,
            ]
        );
    }

    public function contactFail(int $processed = 1): void
    {
        ContactImport::query()->where(
            [
                'id' => $this->uuid()->toString(),
            ],
        )->incrementEach(
            [
                'total_processed' => $processed,
                'errors' => $processed,
            ]
        );
    }

    public function contactSuccess(int $processed = 1): void
    {
        ContactImport::query()->where(
            [
                'id' => $this->uuid()->toString(),
            ],
        )->incrementEach(
            [
                'total_processed' => $processed,
            ]
        );
    }
}
