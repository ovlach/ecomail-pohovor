<?php

declare(strict_types=1);

namespace App\Services\ProcessImportMonitor;

use Ramsey\Uuid\UuidInterface;

/**
 * Handling actual state of import
 *
 * Never use not-serializable values in implementations!
 */
interface ProcessImportState
{
    public function uuid(): UuidInterface;

    public function start(): void;

    public function finish(): void;

    public function fail(): void;

    public function contactFail(int $processed = 1): void;

    public function contactDuplicate(int $processed = 1): void;

    public function contactSuccess(int $processed = 1): void;
}
