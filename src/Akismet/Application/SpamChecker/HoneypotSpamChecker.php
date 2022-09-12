<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker;

use Sulu\Bundle\FormBundle\Configuration\FormConfigurationInterface;
use Symfony\Component\Form\FormInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamException;

class HoneypotSpamChecker implements SpamCheckerInterface
{
    public const SPAM_REASON_HONEYPOT = 'honeypot';

    private ?string $honeyPotField;
    private string $honeyPotSpamStrategy;

    public function __construct(?string $honeyPotField, ?string $honeyPotSpamStrategy)
    {
        $this->honeyPotField = $honeyPotField;
        $this->honeyPotSpamStrategy = $honeyPotSpamStrategy ?? self::SPAM_STRATEGY_SPAM;
    }

    public function check(FormInterface $form, FormConfigurationInterface $formConfiguration): void
    {
        if (!$this->honeyPotField) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $honeypotFieldName = str_replace(' ', '_', strtolower($this->honeyPotField));

        if (!$form->has($honeypotFieldName)) {
            return;
        }

        $honeypotField = $form->get($honeypotFieldName);

        if (!$honeypotField->getData()) {
            return;
        }

        throw new FormSubmissionIsSpamException($this->honeyPotSpamStrategy, self::SPAM_REASON_HONEYPOT);
    }
}
