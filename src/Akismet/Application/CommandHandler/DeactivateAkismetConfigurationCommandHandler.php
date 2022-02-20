<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeactivateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class DeactivateAkismetConfigurationCommandHandler implements MessageHandlerInterface
{
    private AkismetConfigurationRepositoryInterface $repository;

    public function __construct(AkismetConfigurationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeactivateAkismetConfigurationCommand $command): AkismetConfigurationInterface
    {
        $id = $command->getId();

        $akismetConfiguration = $this->repository->getById($id);
        $akismetConfiguration->setActive(false);

        return $akismetConfiguration;
    }
}
