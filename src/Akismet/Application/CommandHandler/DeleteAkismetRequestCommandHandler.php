<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetRequestCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

final class DeleteAkismetRequestCommandHandler implements MessageHandlerInterface
{
    private AkismetRequestRepositoryInterface $repository;

    public function __construct(AkismetRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeleteAkismetRequestCommand $command): void
    {
        $id = $command->getId();

        $akismetRequest = $this->repository->getById($id);
        $this->repository->remove($akismetRequest);
    }
}
