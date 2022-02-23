<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsHamCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsHamCommand
 */
class MarkAkismetRequestAsHamCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createMarkAkismetRequestAsHamCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createMarkAkismetRequestAsHamCommand(array $data = []): MarkAkismetRequestAsHamCommand
    {
        return new MarkAkismetRequestAsHamCommand($data['id'] ?? 1);
    }
}
