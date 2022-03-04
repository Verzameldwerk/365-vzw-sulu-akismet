<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindOrCreateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class FindOrCreateAkismetConfigurationCommandHandler implements MessageHandlerInterface
{
    private AkismetConfigurationRepositoryInterface $repository;

    public function __construct(AkismetConfigurationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(FindOrCreateAkismetConfigurationCommand $command): AkismetConfigurationInterface
    {
        $formId = $command->getFormId();

        try {
            return $this->repository->getByFormId($formId);
        } catch (AkismetConfigurationNotFoundException $e) {
            $akismetConfiguration = $this->repository->create($formId);
            $this->repository->add($akismetConfiguration);

            return $akismetConfiguration;
        }
    }
}
