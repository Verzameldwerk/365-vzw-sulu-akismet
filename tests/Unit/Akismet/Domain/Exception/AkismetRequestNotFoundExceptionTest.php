<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Domain\Exception;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetRequestNotFoundException;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetRequestNotFoundException
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException
 */
class AkismetRequestNotFoundExceptionTest extends ModelNotFoundExceptionTest
{
    public function testGetModelName(): void
    {
        $exception = $this->createModelNotFoundException();
        self::assertSame('AkismetRequest', $exception::getModelName());
    }

    /**
     * @param array{
     *     criteria?: array<string, mixed>,
     * } $data
     */
    protected function createModelNotFoundException(array $data = []): AkismetRequestNotFoundException
    {
        return new AkismetRequestNotFoundException(
            $data['criteria'] ?? ['id' => 1],
        );
    }
}
