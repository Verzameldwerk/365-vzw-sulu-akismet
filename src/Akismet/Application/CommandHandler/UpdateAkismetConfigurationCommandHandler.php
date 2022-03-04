<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapperInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException;
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
        $previousActiveState = $akismetConfiguration->isActive();

        $this->dataMapper->mapData($akismetConfiguration, $data);
        $newActiveState = $akismetConfiguration->isActive();

        if (!$akismetConfiguration->getApiKey()) {
            if (!$previousActiveState && $newActiveState) {
                throw new AkismetApiException('Cannot activate akismet configuration, if api key is empty');
            }

            $akismetConfiguration->setActive(false);
        }

        if (!$akismetConfiguration->getSiteUrl()) {
            if (!$previousActiveState && $newActiveState) {
                throw new AkismetApiException('Cannot activate akismet configuration, if site url is empty');
            }

            $akismetConfiguration->setActive(false);
        }

        if ($akismetConfiguration->getApiKey() && $akismetConfiguration->getSiteUrl()) {
            $this->api->verifyKey($akismetConfiguration);
        }

        return $akismetConfiguration;
    }
}
