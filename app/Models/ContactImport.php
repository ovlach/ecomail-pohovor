<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactImportStateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property \Illuminate\Support\Carbon $queue_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property int $total_processed
 * @property int $errors
 * @property int $duplicates
 * @property string $file
 * @property \App\Enums\ContactImportStateEnum $state
 */
class ContactImport extends Model
{
    /** @use HasFactory<\Database\Factories\ContactImportFactory> */
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'queue_at',
        'started_at',
        'finished_at',
        'total_processed',
        'errors',
        'duplicates',
        'state',
        'file',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'queue_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'state' => ContactImportStateEnum::class,
        ];
    }
}
