<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class FindAkismetConfigurationCommandHandler implements MessageHandlerInterface
{
    private AkismetConfigurationRepositoryInterface $repository;

    public function __construct(AkismetConfigurationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(FindAkismetConfigurationCommand $command): AkismetConfigurationInterface
    {
        $id = $command->getId();

        return $this->repository->getById($id);
    }
}
