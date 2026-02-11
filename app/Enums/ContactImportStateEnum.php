<?php

declare(strict_types=1);

namespace App\Enums;

enum ContactImportStateEnum: string
{
    case Running = 'RUNNING';
    case Done = 'DONE';
    case Pending = 'PENDING';
    case Fail = 'FAILED';
}
