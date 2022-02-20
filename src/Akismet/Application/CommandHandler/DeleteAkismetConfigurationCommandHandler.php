<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class DeleteAkismetConfigurationCommandHandler implements MessageHandlerInterface
{
    private AkismetConfigurationRepositoryInterface $repository;

    public function __construct(AkismetConfigurationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeleteAkismetConfigurationCommand $command): void
    {
        $id = $command->getId();

        $akismetConfiguration = $this->repository->getById($id);
        $this->repository->remove($akismetConfiguration);
    }
}
