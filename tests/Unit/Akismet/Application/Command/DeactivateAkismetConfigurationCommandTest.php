<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeactivateAkismetConfigurationCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeactivateAkismetConfigurationCommand
 */
class DeactivateAkismetConfigurationCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createDeactivateAkismetConfigurationCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createDeactivateAkismetConfigurationCommand(array $data = []): DeactivateAkismetConfigurationCommand
    {
        return new DeactivateAkismetConfigurationCommand($data['id'] ?? 1);
    }
}
