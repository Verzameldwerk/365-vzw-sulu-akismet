<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapperInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class UpdateAkismetConfigurationCommandHandler implements MessageHandlerInterface
{
    private AkismetConfigurationRepositoryInterface $repository;
    private AkismetConfigurationDataMapperInterface $dataMapper;
    private AkismetApiInterface $api;

    public function __construct(
        AkismetConfigurationRepositoryInterface $repository,
        AkismetConfigurationDataMapperInterface $dataMapper,
        AkismetApiInterface $api
    ) {
        $this->repository = $repository;
        $this->dataMapper = $dataMapper;
        $this->api = $api;
    }

    public function __invoke(UpdateAkismetConfigurationCommand $command): AkismetConfigurationInterface
    {
        $id = $command->getId();
        $data = $command->getData();

        $akismetConfiguration = $this->repository->getById($id);
        $this->dataMapper->mapData($akismetConfiguration, $data);

        if (!$akismetConfiguration->getApiKey() || !$akismetConfiguration->getSiteUrl()) {
            $akismetConfiguration->setActive(false);

            return $akismetConfiguration;
        }

        $this->api->verifyKey($akismetConfiguration);

        return $akismetConfiguration;
    }
}
