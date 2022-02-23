<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\ActivateAkismetConfigurationCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\ActivateAkismetConfigurationCommand
 */
class ActivateAkismetConfigurationCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createActivateAkismetConfigurationCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createActivateAkismetConfigurationCommand(array $data = []): ActivateAkismetConfigurationCommand
    {
        return new ActivateAkismetConfigurationCommand($data['id'] ?? 1);
    }
}
