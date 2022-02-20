<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Symfony\Form;

use Psr\Log\LoggerInterface;
use Sulu\Bundle\FormBundle\Entity\Dynamic;
use Sulu\Bundle\FormBundle\Form\Type\DynamicFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetRequestCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\CreateAkismetRequestCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver\AkismetParamsResolverInterface;

final class AkismetFormTypeExtension extends AbstractTypeExtension
{
    private MessageBusInterface $messageBus;
    private AkismetParamsResolverInterface $paramsResolver;
    private ?LoggerInterface $logger;

    public function __construct(
        MessageBusInterface $messageBus,
        AkismetParamsResolverInterface $paramsResolver,
        ?LoggerInterface $logger
    ) {
        $this->messageBus = $messageBus;
        $this->paramsResolver = $paramsResolver;
        $this->logger = $logger;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (PostSubmitEvent $event) {
            $form = $event->getForm();
            $data = $form->getData();

            if (!$form->isSubmitted() || !$form->isValid() || !$data instanceof Dynamic) {
                return;
            }

            $suluFormId = $data->getForm()->getId();
            if (!$suluFormId) {
                return;
            }

            $params = $this->paramsResolver->resolve($form);

            try {
                /* @see CreateAkismetRequestCommandHandler::__invoke() */
                $this->messageBus->dispatch(
                    new CreateAkismetRequestCommand($suluFormId, $params)
                );
            } catch (\Throwable $e) { // @phpstan-ignore-line
                if (null !== $this->logger) {
                    $this->logger->error($e->getMessage(), ['exception' => $e]);
                }
            }
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [DynamicFormType::class];
    }
}
