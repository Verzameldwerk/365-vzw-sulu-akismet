<?php

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Sulu\Form;

use Sulu\Bundle\FormBundle\Configuration\FormConfigurationInterface;
use Sulu\Bundle\FormBundle\Form\Handler;
use Sulu\Bundle\FormBundle\Form\HandlerInterface;
use Symfony\Component\Form\FormInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\SpamCheckerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamExceptionInterface;

class AkismetFormHandler implements HandlerInterface
{
    private HandlerInterface $decorated;
    private SpamCheckerInterface $spamChecker;
    private string $honeyPotField;

    public function __construct(HandlerInterface $decorated, SpamCheckerInterface $spamChecker, ?string $honeyPotField)
    {
        $this->decorated = $decorated;
        $this->spamChecker = $spamChecker;
        $this->honeyPotField = str_replace(' ', '_', strtolower($honeyPotField ?? ''));
    }

    public function handle(FormInterface $form, FormConfigurationInterface $configuration): bool
    {
        if (!$form->isValid()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $handler = $this->decorated;

        try {
            $this->spamChecker->check($form, $configuration);
        } catch (FormSubmissionIsSpamExceptionInterface $e) {
            if (!$handler instanceof Handler || !$this->honeyPotField || !$form->has($this->honeyPotField)) {
                // @codeCoverageIgnoreStart
                return true;
                // @codeCoverageIgnoreEnd
            }

            $this->setHoneypotStrategyOnDecoratedHandler($handler, $e->getSpamStrategy());
            $honeyPotFormField = $form->get($this->honeyPotField);

            if (!$honeyPotFormField->getData()) {
                $this->setHoneypotFormFieldData($form->get($this->honeyPotField), $e->getReason());
            }
        }

        return $handler->handle($form, $configuration);
    }

    private function setHoneypotStrategyOnDecoratedHandler(Handler $handler, string $strategy): void
    {
        $reflClass = new \ReflectionClass($handler);

        if (!$reflClass->hasProperty('honeyPotStrategy')) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $reflProperty = $reflClass->getProperty('honeyPotStrategy');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($handler, $strategy);
    }

    private function setHoneypotFormFieldData(FormInterface $honeyPotFormField, string $value): void
    {
        $reflClass = new \ReflectionClass($honeyPotFormField);

        if (!$reflClass->hasProperty('modelData')) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $reflProperty = $reflClass->getProperty('modelData');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($honeyPotFormField, $value);
    }
}
