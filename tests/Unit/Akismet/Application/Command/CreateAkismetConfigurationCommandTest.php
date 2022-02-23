<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetConfigurationCommand;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetConfigurationCommand
 */
class CreateAkismetConfigurationCommandTest extends TestCase
{
    public function testGetFormId(): void
    {
        $command = $this->createCreateAkismetConfigurationCommand(['formId' => 1]);
        self::assertSame(1, $command->getFormId());
    }

    public function testGetData(): void
    {
        $command = $this->createCreateAkismetConfigurationCommand(['data' => ['foo' => 'bar']]);
        self::assertSame(['foo' => 'bar'], $command->getData());
    }

    /**
     * @param array{
     *     formId?: int,
     *     data?: mixed[],
     * } $data
     */
    private function createCreateAkismetConfigurationCommand(array $data = []): CreateAkismetConfigurationCommand
    {
        return new CreateAkismetConfigurationCommand(
            $data['formId'] ?? 1,
            $data['data'] ?? ['foo' => 'bar'],
        );
    }
}
