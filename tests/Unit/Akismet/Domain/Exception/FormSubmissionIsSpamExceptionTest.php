<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamException;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamException
 */
class FormSubmissionIsSpamExceptionTest extends TestCase
{
    public function testGetMessage(): void
    {
        $exception = $this->createFormSubmissionIsSpamException([
            'spamStrategy' => 'SPAM_STRATEGY',
            'reason' => 'REASON',
        ]);

        self::assertSame(
            'FormSubmission has been marked as spam because of "REASON" with strategy "SPAM_STRATEGY"',
            $exception->getMessage()
        );
    }

    public function testGetSpamStrategy(): void
    {
        $exception = $this->createFormSubmissionIsSpamException([
            'spamStrategy' => 'SPAM_STRATEGY',
        ]);

        self::assertSame(
            'SPAM_STRATEGY',
            $exception->getSpamStrategy()
        );
    }

    public function testGetReason(): void
    {
        $exception = $this->createFormSubmissionIsSpamException([
            'reason' => 'REASON',
        ]);

        self::assertSame(
            'REASON',
            $exception->getReason()
        );
    }

    /**
     * @param array{
     *     spamStrategy?: string,
     *     reason?: string,
     * } $data
     */
    protected function createFormSubmissionIsSpamException(array $data = []): FormSubmissionIsSpamException
    {
        return new FormSubmissionIsSpamException(
            $data['spamStrategy'] ?? 'SPAM_STRATEGY',
            $data['reason'] ?? 'REASON',
        );
    }
}
