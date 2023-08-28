<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker;

use Sulu\Bundle\FormBundle\Configuration\FormConfigurationInterface;
use Symfony\Component\Form\FormInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamExceptionInterface;

class SpamChecker implements SpamCheckerInterface
{
    protected const PRIORITIZED_SPAM_STRATEGIES = [
        self::SPAM_STRATEGY_NO_SAVE => 30,
        self::SPAM_STRATEGY_NO_EMAIL => 20,
        self::SPAM_STRATEGY_SPAM => 10,
    ];

    /**
     * @var iterable<SpamCheckerInterface>
     */
    private iterable $spamCheckers;

    /**
     * @param iterable<SpamCheckerInterface> $spamCheckers
     */
    public function __construct(iterable $spamCheckers)
    {
        $this->spamCheckers = $spamCheckers;
    }

    public function check(FormInterface $form, FormConfigurationInterface $formConfiguration): void
    {
        $highestPriority = 0;
        $exception = null;

        foreach ($this->spamCheckers as $spamChecker) {
            if ($this === $spamChecker) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            try {
                $spamChecker->check($form, $formConfiguration);
            } catch (FormSubmissionIsSpamExceptionInterface $e) {
                $priority = self::getPriorityForException($e);

                if (null === $exception || $priority > $highestPriority) {
                    $exception = $e;
                    $highestPriority = $priority;
                }
            }
        }

        if (null !== $exception) {
            throw $exception;
        }
    }

    protected static function getPriorityForException(FormSubmissionIsSpamExceptionInterface $e): int
    {
        $spamStrategy = $e->getSpamStrategy();

        return static::PRIORITIZED_SPAM_STRATEGIES[$spamStrategy] ?? 0;
    }
}
