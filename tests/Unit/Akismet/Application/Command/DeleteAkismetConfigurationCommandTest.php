<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetConfigurationCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetConfigurationCommand
 */
class DeleteAkismetConfigurationCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createDeleteAkismetConfigurationCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createDeleteAkismetConfigurationCommand(array $data = []): DeleteAkismetConfigurationCommand
    {
        return new DeleteAkismetConfigurationCommand($data['id'] ?? 1);
    }
}
