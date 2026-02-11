<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Data\Command\BatchResult;
use App\Data\Intent\Contact\CreateContactIntent;
use App\Jobs\ProcessImport;
use App\Services\Command\CreateContactCommand;
use App\Services\ProcessImportMonitor\ProcessImportState;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ProcessImportTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $tempFiles = [];

    public function testHandleValidFile(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <item>
    <email>god@foundation.edu</email>
    <first_name>Hari</first_name>
    <last_name>Seldon</last_name>
  </item>
  <item>
    <email>picard@federation.earth</email>
    <first_name>Jean-Luc</first_name>
    <last_name>Picard</last_name>
  </item>
  <item>
    <email>jackwithapostrophe@sgc.mil</email>
    <first_name>Jack</first_name>
    <last_name>O'Neill</last_name>
  </item>
</data>
XML;

        $createContactCommand = $this->createCreateContactCommandMock(
            1,
            ['god@foundation.edu', 'picard@federation.earth', 'jackwithapostrophe@sgc.mil'],
            new BatchResult(0, 0, 3),
        );

        $processImportState = $this->createMock(ProcessImportState::class);
        $processImportState->expects($this->exactly(1))->method('start');
        $processImportState->expects($this->exactly(1))->method('finish');
        $processImportState->expects($this->exactly(1))->method('contactFail')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactDuplicate')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactSuccess')->with(3);
        $processImportState->method('uuid')->willReturn(Uuid::uuid7());

        $job = new ProcessImport(
            $this->createTempFile($xml),
            $processImportState,
        );
        $job->handle($createContactCommand);
    }

    public function testHandleMultipleTextNodes(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <item>
    <email>god@<![CDATA[foundati]]>on.edu</email>
    <first_name>Hari</first_name>
    <last_name>Seldon</last_name>
  </item>
</data>
XML;

        $createContactCommand = $this->createCreateContactCommandMock(
            1,
            ['god@foundation.edu'],
            new BatchResult(0, 0, 1),
        );

        $processImportState = $this->createMock(ProcessImportState::class);
        $processImportState->expects($this->exactly(1))->method('start');
        $processImportState->expects($this->exactly(1))->method('finish');
        $processImportState->expects($this->exactly(1))->method('contactFail')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactDuplicate')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactSuccess')->with(1);
        $processImportState->method('uuid')->willReturn(Uuid::uuid7());

        $job = new ProcessImport(
            $this->createTempFile($xml),
            $processImportState,
        );
        $job->handle($createContactCommand);
    }

    public function testHandleValidMultipleBatches(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><data>';
        $emails = [];
        for ($i = 0; $i <= 5001; $i++) {
            $xml .= '<item>
                    <email>god' . $i . '@foundation.edu</email>
                    <first_name>Hari</first_name>
                    <last_name>Seldon</last_name>
                </item>';
            $emails[] = 'god@foundation.edu' . $i;
        }
        $xml .= '</data>';

        $createContactCommand = $this->createCreateContactCommandMock(
            2,
            [],
            [
                new BatchResult(0, 0, 5000),
                new BatchResult(0, 0, 1),
            ],
        );
        $processImportState = $this->createMock(ProcessImportState::class);
        $processImportState->expects($this->exactly(1))->method('start');
        $processImportState->expects($this->exactly(1))->method('finish');
        $processImportState->expects($this->exactly(2))->method('contactFail')->with(0);
        $processImportState->expects($this->exactly(2))
            ->method('contactDuplicate')
            ->with($this->callback(static fn (int $duplicates): bool => $duplicates === 0 || $duplicates === 1));
        $processImportState->expects($this->exactly(2))
            ->method('contactSuccess')
            ->with($this->callback(static fn (int $processed): bool => $processed === 5000 || $processed === 1));
        $processImportState->method('uuid')->willReturn(Uuid::uuid7());

        $job = new ProcessImport(
            $this->createTempFile($xml),
            $processImportState,
        );
        $job->handle($createContactCommand);
    }

    public function testHandleInvalidFile(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <item>
    <first_name>Hari</first_name>
    <last_name>Seldon</last_name>
  </item>
XML;

        $processImportState = $this->createMock(ProcessImportState::class);
        $processImportState->expects($this->exactly(1))->method('start');
        $processImportState->expects($this->exactly(1))->method('finish');
        $failCallsWithOne = 0;
        $processImportState->expects($this->atLeastOnce())
            ->method('contactFail')
            ->willReturnCallback(function (int $count) use (&$failCallsWithOne): void {
                if ($count === 1) {
                    $failCallsWithOne++;
                }
            });
        $processImportState->expects($this->exactly(1))->method('contactDuplicate')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactSuccess')->with(0);
        $processImportState->method('uuid')->willReturn(Uuid::uuid7());

        $job = new ProcessImport(
            $this->createTempFile($xml),
            $processImportState,
        );
        $job->handle($this->createCreateContactCommandMock());
        $this->assertGreaterThan(0, $failCallsWithOne);
    }

    public function testHandleInvalidStructure(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
  <item>
    <email>god@foundation.edu</email>
    <first_name>Hari</first_name>
    <last_name>Seldon</last_name>
  </item>
  <foo>
     <email>anubis@badguy.space</email>
    <first_name>No-imported</first_name>
    <last_name>Anubis</last_name>
  </foo>
</data>
XML;

        $createContactCommand = $this->createCreateContactCommandMock(
            1,
            ['god@foundation.edu'],
            new BatchResult(0, 0, 1),
        );
        $processImportState = $this->createMock(ProcessImportState::class);
        $processImportState->expects($this->exactly(1))->method('start');
        $processImportState->expects($this->exactly(1))->method('finish');
        $processImportState->expects($this->exactly(1))->method('contactFail')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactDuplicate')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactSuccess')->with(1);

        $job = new ProcessImport(
            $this->createTempFile($xml),
            $processImportState,
        );
        $job->handle($createContactCommand);
    }

    public function testHandleReallyBadXml(): void
    {
        $xml = 'NOT XML';

        $createContactCommand = $this->createCreateContactCommandMock(
            1,
            [],
            new BatchResult(0, 0, 0),
        );
        $processImportState = $this->createMock(ProcessImportState::class);
        $processImportState->expects($this->exactly(1))->method('start');
        $processImportState->expects($this->exactly(1))->method('finish');
        $processImportState->expects($this->exactly(1))->method('contactFail')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactDuplicate')->with(0);
        $processImportState->expects($this->exactly(1))->method('contactSuccess')->with(0);

        $job = new ProcessImport(
            $this->createTempFile($xml),
            $processImportState,
        );
        $job->handle($createContactCommand);
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->delete($this->tempFiles);

        $this->tempFiles = [];

        parent::tearDown();
    }

    private function createTempFile(string $contents): string
    {
        $path = 'process-import/' . Uuid::uuid7()->toString() . '.xml';
        Storage::disk('local')->put($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }

    private function createCreateContactCommandMock(
        ?int $expectedCalls = null,
        array $emails = [],
        BatchResult|array|null $batchResult = null,
    ): CreateContactCommand {
        $createContactCommand = $this->createMock(CreateContactCommand::class);

        $expectation = $expectedCalls !== null
            ? $createContactCommand->expects($this->exactly($expectedCalls))->method('multipleExecute')
            : $createContactCommand->method('multipleExecute');

        if ($expectedCalls !== null) {
            $expectation
                ->with($this->callback(function (array $intents) use ($emails): bool {
                    if ($emails === []) {
                        return true;
                    }

                    $argEmails = array_map(
                        static fn (CreateContactIntent $intent): string => $intent->email,
                        $intents,
                    );
                    sort($argEmails);
                    sort($emails);

                    return $argEmails === $emails;
                }));
        }

        if (is_array($batchResult)) {
            $expectation->willReturnOnConsecutiveCalls(...$batchResult);
        } elseif ($batchResult instanceof BatchResult) {
            $expectation->willReturn($batchResult);
        } else {
            $expectation->willReturn(new BatchResult(0, 0, 0));
        }

        return $createContactCommand;
    }
}
