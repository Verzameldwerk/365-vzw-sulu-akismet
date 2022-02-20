<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\ActivateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class ActivateAkismetConfigurationCommandHandler implements MessageHandlerInterface
{
    private AkismetConfigurationRepositoryInterface $repository;
    private AkismetApiInterface $api;

    public function __construct(
        AkismetConfigurationRepositoryInterface $repository,
        AkismetApiInterface $api
    ) {
        $this->repository = $repository;
        $this->api = $api;
    }

    public function __invoke(ActivateAkismetConfigurationCommand $command): AkismetConfigurationInterface
    {
        $id = $command->getId();

        $akismetConfiguration = $this->repository->getById($id);
        $akismetConfiguration->setActive(true);

        $this->api->verifyKey($akismetConfiguration);

        return $akismetConfiguration;
    }
}
