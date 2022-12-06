<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindAkismetConfigurationCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindAkismetConfigurationCommand
 */
class FindAkismetConfigurationCommandTest extends TestCase
{
    public function testGetId(): void
    {
        $command = $this->createFindAkismetConfigurationCommand(['id' => 1]);
        self::assertSame(1, $command->getId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createFindAkismetConfigurationCommand(array $data = []): FindAkismetConfigurationCommand
    {
        return new FindAkismetConfigurationCommand(
            $data['id'] ?? 1,
        );
    }
}
