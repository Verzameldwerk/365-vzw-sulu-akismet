<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapperInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class CreateAkismetConfigurationCommandHandler implements MessageHandlerInterface
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

    public function __invoke(CreateAkismetConfigurationCommand $command): AkismetConfigurationInterface
    {
        $formId = $command->getFormId();
        $data = $command->getData();

        try {
            $akismetConfiguration = $this->repository->getByFormId($formId);
        } catch (AkismetConfigurationNotFoundException $e) {
            $akismetConfiguration = $this->repository->create($formId);
            $this->repository->add($akismetConfiguration);
        }

        $this->dataMapper->mapData($akismetConfiguration, $data);

        if ($akismetConfiguration->getApiKey() && $akismetConfiguration->getSiteUrl()) {
            $this->api->verifyKey($akismetConfiguration);
        }

        return $akismetConfiguration;
    }
}
