<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception;

class FormSubmissionIsSpamException extends \Exception implements FormSubmissionIsSpamExceptionInterface
{
    private string $spamStrategy;
    private string $reason;

    /**
     * @see SpamCheckerInterface::SPAM_STRATEGY_SPAM
     * @see SpamCheckerInterface::SPAM_STRATEGY_NO_SAVE
     * @see SpamCheckerInterface::SPAM_STRATEGY_NO_EMAIL
     */
    public function __construct(string $spamStrategy, string $reason)
    {
        $this->spamStrategy = $spamStrategy;
        $this->reason = $reason;

        parent::__construct(
            sprintf('FormSubmission has been marked as spam because of "%s" with strategy "%s"', $reason, $spamStrategy)
        );
    }

    public function getSpamStrategy(): string
    {
        return $this->spamStrategy;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
