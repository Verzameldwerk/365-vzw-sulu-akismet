<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker;

use Psr\Log\LoggerInterface;
use Sulu\Bundle\FormBundle\Configuration\FormConfigurationInterface;
use Sulu\Bundle\FormBundle\Entity\Dynamic;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetRequestCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver\AkismetParamsResolverInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

class AkismetSpamChecker implements SpamCheckerInterface
{
    public const SPAM_REASON_AKISMET = 'akismet';

    private MessageBusInterface $messageBus;
    private AkismetConfigurationRepositoryInterface $repository;
    private AkismetParamsResolverInterface $paramsResolver;
    private ?LoggerInterface $logger;
    private string $akismetSpamStrategy;
    private bool $debug;

    public function __construct(
        MessageBusInterface $messageBus,
        AkismetConfigurationRepositoryInterface $repository,
        AkismetParamsResolverInterface $paramsResolver,
        ?LoggerInterface $logger,
        ?string $akismetSpamStrategy,
        bool $debug
    ) {
        $this->messageBus = $messageBus;
        $this->repository = $repository;
        $this->paramsResolver = $paramsResolver;
        $this->logger = $logger;
        $this->akismetSpamStrategy = $akismetSpamStrategy ?? self::SPAM_STRATEGY_SPAM;
        $this->debug = $debug;
    }

    public function check(FormInterface $form, FormConfigurationInterface $formConfiguration): void
    {
        $isSpam = $this->checkForm($form);

        if (!$isSpam) {
            return;
        }

        throw new FormSubmissionIsSpamException($this->akismetSpamStrategy, self::SPAM_REASON_AKISMET);
    }

    public function checkForm(FormInterface $form): bool
    {
        $data = $form->getData();

        if (!$form->isSubmitted() || !$form->isValid() || !$data instanceof Dynamic) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $suluFormId = $data->getForm()->getId();
        if (!$suluFormId) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        try {
            $akismetConfiguration = $this->repository->getByFormId($suluFormId);
        } catch (AkismetConfigurationNotFoundException $e) {
            return false;
        }

        if (!$akismetConfiguration->isActive()) {
            return false;
        }

        try {
            $params = $this->paramsResolver->resolve($form, $suluFormId);

            $message = new CreateAkismetRequestCommand($suluFormId, $params);

            /* @see CreateAkismetRequestCommandHandler::__invoke() */
            $envelope = $this->messageBus->dispatch($message);

            /** @var HandledStamp[] $handledStamps */
            $handledStamps = $envelope->all(HandledStamp::class);

            if (!$handledStamps) {
                // Early return because transport is async
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            if (\count($handledStamps) > 1) {
                // @codeCoverageIgnoreStart
                $handlers = implode(', ', array_map(function (HandledStamp $stamp): string {
                    return sprintf('"%s"', $stamp->getHandlerName());
                }, $handledStamps));

                throw new LogicException(sprintf('Message of type "%s" was handled multiple times. Only one handler is expected when using "%s::%s()", got %d: %s.', get_debug_type($envelope->getMessage()), static::class, __FUNCTION__, \count($handledStamps), $handlers));
                // @codeCoverageIgnoreEnd
            }

            /** @var bool $isSpam */
            $isSpam = $handledStamps[0]->getResult();

            return $isSpam;
        } catch (\Throwable $e) { // @codeCoverageIgnore
            // @codeCoverageIgnoreStart
            if ($this->debug) {
                throw $e;
            }

            if (null !== $this->logger) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            }

            return false;
            // @codeCoverageIgnoreEnd
        }
    }
}
