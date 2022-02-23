<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetRequestCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetRequestCommand
 */
class DeleteAkismetRequestCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createDeleteAkismetRequestCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createDeleteAkismetRequestCommand(array $data = []): DeleteAkismetRequestCommand
    {
        return new DeleteAkismetRequestCommand($data['id'] ?? 1);
    }
}
