<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Domain\Exception;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException
 */
class AkismetConfigurationNotFoundExceptionTest extends ModelNotFoundExceptionTest
{
    public function testGetModelName(): void
    {
        $exception = $this->createModelNotFoundException();
        self::assertSame('AkismetConfiguration', $exception::getModelName());
    }

    /**
     * @param array{
     *     criteria?: array<string, mixed>,
     * } $data
     */
    protected function createModelNotFoundException(array $data = []): AkismetConfigurationNotFoundException
    {
        return new AkismetConfigurationNotFoundException(
            $data['criteria'] ?? ['id' => 1],
        );
    }
}
