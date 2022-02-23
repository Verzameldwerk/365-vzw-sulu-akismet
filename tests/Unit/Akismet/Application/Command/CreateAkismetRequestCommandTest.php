<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetRequestCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetRequestCommand
 */
class CreateAkismetRequestCommandTest extends TestCase
{
    public function testGetFormId(): void
    {
        $command = $this->createCreateAkismetRequestCommand(['formId' => 1]);
        self::assertSame(1, $command->getFormId());
    }

    public function testGetParams(): void
    {
        $command = $this->createCreateAkismetRequestCommand(['params' => ['foo' => 'bar']]);
        self::assertSame(['foo' => 'bar'], $command->getParams());
    }

    /**
     * @param array{
     *     formId?: int,
     *     data?: mixed[],
     * } $data
     */
    private function createCreateAkismetRequestCommand(array $data = []): CreateAkismetRequestCommand
    {
        return new CreateAkismetRequestCommand(
            $data['formId'] ?? 1,
            $data['data'] ?? ['foo' => 'bar'],
        );
    }
}
