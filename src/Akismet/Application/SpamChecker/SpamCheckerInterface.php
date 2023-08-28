<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker;

use Sulu\Bundle\FormBundle\Configuration\FormConfigurationInterface;
use Symfony\Component\Form\FormInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamExceptionInterface;

interface SpamCheckerInterface
{
    public const SPAM_STRATEGY_SPAM = 'spam';
    public const SPAM_STRATEGY_NO_SAVE = 'no_save';
    public const SPAM_STRATEGY_NO_EMAIL = 'no_email';

    public const SPAM_STRATEGIES = [
        self::SPAM_STRATEGY_SPAM,
        self::SPAM_STRATEGY_NO_SAVE,
        self::SPAM_STRATEGY_NO_EMAIL,
    ];

    /**
     * @throws FormSubmissionIsSpamExceptionInterface
     */
    public function check(FormInterface $form, FormConfigurationInterface $formConfiguration): void;
}
