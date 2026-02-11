<?php

declare(strict_types=1);

namespace App\Data;

use Throwable;

class InvariantException extends \InvalidArgumentException
{
    /**
     * @param array<string> $fields
     */
    public function __construct(string $message, public readonly array $fields, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
