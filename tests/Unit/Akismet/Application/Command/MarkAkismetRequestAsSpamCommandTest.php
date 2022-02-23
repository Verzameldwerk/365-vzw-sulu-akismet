<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsSpamCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsSpamCommand
 */
class MarkAkismetRequestAsSpamCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createMarkAkismetRequestAsSpamCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createMarkAkismetRequestAsSpamCommand(array $data = []): MarkAkismetRequestAsSpamCommand
    {
        return new MarkAkismetRequestAsSpamCommand($data['id'] ?? 1);
    }
}
