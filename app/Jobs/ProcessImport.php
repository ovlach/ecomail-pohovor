<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Data\Intent\Contact\CreateContactIntent;
use App\Data\Intent\Contact\ProcessImport\ImportContactIntent;
use App\Services\Command\CreateContactCommand;
use App\Services\ProcessImportMonitor\ProcessImportState;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use XMLReader;

class ProcessImport implements ShouldQueue
{
    use Queueable;

    private const STATE_DATA = 1 << 0;

    private const STATE_ITEM = 1 << 1;

    private const MAX_BATCH_SIZE = 5000;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $file,
        private readonly ProcessImportState $processImportState,
    ) {
    }

    public function handle(
        CreateContactCommand $createContactCommand,
    ): void {
        $prev = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $this->runImport($createContactCommand);
        } catch (\Exception $e) {
            $this->processImportState->fail();
            throw $e;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
    }

    /**
     * Execute the job.
     */
    private function runImport(
        CreateContactCommand $createContactCommand,
    ): void {
        $importPath = Storage::disk('local')->path($this->file);
        $reader = \XMLReader::open($importPath);
        Log::debug('job {uuid} - start', [
            'uuid' => $this->processImportState->uuid()->toString(),
        ]);
        if ($reader === false) {
            Log::error('job {uuid} - cannot create reader {file}', [
                'uuid' => $this->processImportState->uuid()->toString(),
                'file' => $importPath,
            ]);

            $this->processImportState->fail();

            return;
        }

        // Yeah, I could use XSD validation -- but would that really help if the XML is only formally correct?
        //  The CS requirements doesn’t mention it, so I chose the ‘import whatever we can, no matter what’ approach.

        $elementDepth = 0;
        $actualState = 0;
        $actualBatch = [];

        $actualContact = null;
        $activeField = null;

        $this->processImportState->start();

        while ($reader->read()) {
            switch ($reader->nodeType) {
                case XMLReader::ELEMENT:
                    $activeField = null;
                    $elementDepth++;
                    if (
                        $elementDepth === 1 && $reader->name === 'data' // data
                    ) {
                        $actualState |= self::STATE_DATA;
                    } elseif (
                        $elementDepth === 2 && $reader->name == 'item' // data/item
                        && ($actualState & self::STATE_DATA) === self::STATE_DATA // we must be in data
                    ) {
                        $actualContact = new ImportContactIntent();
                        $actualState |= self::STATE_ITEM;
                        libxml_clear_errors();
                    } elseif (
                        $elementDepth === 3 // data/item/element
                        && ($actualState & self::STATE_ITEM) === self::STATE_ITEM // We must be in item
                        && $actualContact !== null // This never happen, but OK
                    ) {
                        switch ($reader->name) {
                            case 'first_name':
                                $activeField = 'first_name';
                                break;
                            case 'last_name':
                                $activeField = 'last_name';
                                break;
                            case 'email':
                                $activeField = 'email';
                                break;
                        }
                    }
                    break;
                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    if ($activeField !== null && $actualContact !== null) {
                        switch ($activeField) {
                            case 'first_name':
                                $value = $reader->value;
                                if ($actualContact->firstName !== null) { // Handles multiple text nodes
                                    $value = $actualContact->firstName . $value;
                                }
                                $actualContact->firstName = $value;
                                break;
                            case 'last_name':
                                $value = $reader->value;
                                if ($actualContact->lastName !== null) { // Handles multiple text nodes
                                    $value = $actualContact->lastName . $value;
                                }
                                $actualContact->lastName = $value;
                                break;
                            case 'email':
                                $value = $reader->value;
                                if ($actualContact->email !== null) { // Handles multiple text nodes
                                    $value = $actualContact->email . $value;
                                }
                                $actualContact->email = $value;
                                break;
                        }
                    }
                    break;
                case XMLReader::END_ELEMENT:
                    $error = libxml_get_last_error();
                    if ($elementDepth === 1 && $reader->name === 'data') {
                        $actualState &= ~self::STATE_DATA;
                    }
                    if ($elementDepth === 2 && $reader->name === 'item') {
                        $itemHasError = false;
                        if ($error !== false) {
                            $itemHasError = true;
                            Log::error(
                                'job {uuid} - importing email failed',
                                [
                                    'uuid' => $this->processImportState->uuid()->toString(),
                                ]
                            );
                            libxml_clear_errors();
                        }
                        if (! $itemHasError) {
                            Log::debug('job {uuid} - importing {email}', [
                                'uuid' => $this->processImportState->uuid()->toString(),
                                'email' => $actualContact?->email,
                            ]);
                            try {
                                if ($actualContact === null) {
                                    Log::error(
                                        'job {uuid} - importing email failed',
                                        [
                                            'uuid' => $this->processImportState->uuid()->toString(),
                                        ]
                                    );
                                    $itemHasError = true;
                                }

                                if (! $itemHasError && $actualContact->email === null) {
                                    $itemHasError = true;
                                }

                                if (! $itemHasError) {
                                    // Omg, PHPStan - this should never happen
                                    $email = $actualContact?->email;
                                    if ($email === null || $actualContact === null) {
                                        $itemHasError = true;
                                    } else {
                                        $intent = new CreateContactIntent(
                                            $email,
                                            $actualContact->firstName,
                                            $actualContact->lastName,
                                        );

                                        $actualBatch[] = $intent;
                                        if (count($actualBatch) >= self::MAX_BATCH_SIZE) {
                                            $this->storeBatch($createContactCommand, $actualBatch);
                                            $actualBatch = [];
                                        }
                                    }
                                }
                            } catch (\InvalidArgumentException $exception) {
                                $itemHasError = true;
                                Log::debug(
                                    'job {uuid} - importing email {email} failed - validation errors',
                                    [
                                        'email' => $actualContact?->email,
                                        'exception' => $exception,
                                    ]
                                );
                            } catch (\Exception $exception) {
                                $itemHasError = true;
                                $this->processImportState->contactFail(count($actualBatch));
                                Log::error(
                                    'job {uuid} - importing email {email} failed critically. skipping batch',
                                    [
                                        'exception' => $exception,
                                    ]
                                );
                                $actualBatch = [];
                            }
                        }

                        if ($itemHasError) {
                            $this->processImportState->contactFail();
                        }

                        $actualState &= ~self::STATE_ITEM;
                        $activeField = null;
                        $actualContact = null;
                    }

                    $elementDepth--;
                    break;
            }
        }

        try {
            $this->storeBatch($createContactCommand, $actualBatch);
        } catch (\Exception $exception) {
            Log::error(
                'job {uuid} - importing email {email} failed critically. skipping batch',
                [
                    'email' => $actualContact?->email,
                    'exception' => $exception,
                ]
            );
            $this->processImportState->contactFail(count($actualBatch));
        }

        Log::debug('job {uuid} - finish', [
            'uuid' => $this->processImportState->uuid()->toString(),
        ]);

        $this->processImportState->finish();
    }

    /**
     * @param  array<CreateContactIntent>  $actualBatch
     */
    private function storeBatch(CreateContactCommand $createContactCommand, array $actualBatch): void
    {
        Log::info('job {uuid} - storing batch', [
            'uuid' => $this->processImportState->uuid()->toString(),
        ]);

        $multipleUpdateResult = $createContactCommand->multipleExecute($actualBatch);
        $this->processImportState->contactDuplicate($multipleUpdateResult->duplicates);
        $this->processImportState->contactSuccess($multipleUpdateResult->success);
        $this->processImportState->contactFail($multipleUpdateResult->fails);
    }
}
