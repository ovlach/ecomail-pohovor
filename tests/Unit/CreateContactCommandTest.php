<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Data\Contact;
use App\Data\Intent\Contact\CreateContactIntent;
use App\Services\Command\CreateContactCommand;
use App\Services\Mapper\ContactMapper;
use App\Services\Storage\ContactStorageProvider;
use Tests\TestCase;

class CreateContactCommandTest extends TestCase
{
    public function testExecuteCreatesContact(): void
    {
        $intent = new CreateContactIntent('jane@example.com', 'Jane', 'Doe');
        $contactMapper = new ContactMapper();

        $contactStorageProvider = $this->createMock(ContactStorageProvider::class);
        $contactStorageProvider->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (Contact $contact): bool {
                return $contact->email === 'jane@example.com'
                    && $contact->firstName === 'Jane'
                    && $contact->lastName === 'Doe';
            }))
            ->willReturnCallback(static fn (Contact $contact): Contact => $contact);

        $command = new CreateContactCommand($contactMapper, $contactStorageProvider);

        $result = $command->execute($intent);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertSame('jane@example.com', $result->email);
    }

    public function testMultipleExecuteCountsFailsAndDuplicates(): void
    {
        $intentA = new CreateContactIntent('a@example.com', 'A', 'User');
        $intentB = new CreateContactIntent('invalid-email', 'B', 'User');
        $contactMapper = new ContactMapper();

        $contactStorageProvider = $this->createMock(ContactStorageProvider::class);
        $contactStorageProvider->expects($this->once())
            ->method('batchCreate')
            ->with($this->callback(function (array $batch): bool {
                if (count($batch) !== 1) {
                    return false;
                }

                return $batch[0] instanceof Contact;
            }))
            ->willReturn(1);

        $command = new CreateContactCommand($contactMapper, $contactStorageProvider);

        $result = $command->multipleExecute([$intentA, $intentB]);

        $this->assertSame(1, $result->fails);
        $this->assertSame(0, $result->duplicates);
        $this->assertSame(1, $result->success);
    }
}
