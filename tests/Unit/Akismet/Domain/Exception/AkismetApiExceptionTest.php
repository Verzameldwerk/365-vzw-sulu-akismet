<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException
 */
class AkismetApiExceptionTest extends TestCase
{
    public function testGetMessage(): void
    {
        $exception = $this->createAkismetApiException(['message' => 'An unexpected error occurred']);
        self::assertSame('An unexpected error occurred', $exception->getMessage());
    }

    public function testGetMessageTranslationKey(): void
    {
        $exception = $this->createAkismetApiException();
        self::assertSame('verzameldwerk_akismet.akismet_error', $exception->getMessageTranslationKey());
    }

    public function testGetMessageTranslationParameters(): void
    {
        $exception = $this->createAkismetApiException(['message' => 'An unexpected error occurred']);
        self::assertSame([
            '{message}' => 'An unexpected error occurred',
        ], $exception->getMessageTranslationParameters());
    }

    /**
     * @param array{
     *     message?: string,
     * } $data
     */
    protected function createAkismetApiException(array $data = []): AkismetApiException
    {
        return new AkismetApiException(
            $data['message'] ?? 'An unexpected error occurred',
        );
    }
}
