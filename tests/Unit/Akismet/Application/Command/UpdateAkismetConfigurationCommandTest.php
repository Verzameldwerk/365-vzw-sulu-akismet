<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand
 */
class UpdateAkismetConfigurationCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createUpdateAkismetConfigurationCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    public function testGetData(): void
    {
        $command = $this->createUpdateAkismetConfigurationCommand(['data' => ['foo' => 'bar']]);
        self::assertSame(['foo' => 'bar'], $command->getData());
    }

    /**
     * @param array{
     *     id?: int,
     *     data?: mixed[],
     * } $data
     */
    private function createUpdateAkismetConfigurationCommand(array $data = []): UpdateAkismetConfigurationCommand
    {
        return new UpdateAkismetConfigurationCommand(
            $data['id'] ?? 1,
            $data['data'] ?? ['foo' => 'bar'],
        );
    }
}
