<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindOrCreateAkismetConfigurationCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindOrCreateAkismetConfigurationCommand
 */
class FindOrCreateAkismetConfigurationCommandTest extends TestCase
{
    public function testGetFormId(): void
    {
        $command = $this->createFindOrCreateAkismetConfigurationCommand(['formId' => 1]);
        self::assertSame(1, $command->getFormId());
    }

    /**
     * @param array{
     *     formId?: int,
     * } $data
     */
    private function createFindOrCreateAkismetConfigurationCommand(array $data = []): FindOrCreateAkismetConfigurationCommand
    {
        return new FindOrCreateAkismetConfigurationCommand(
            $data['formId'] ?? 1,
        );
    }
}
