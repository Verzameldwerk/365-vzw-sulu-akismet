<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetRequestCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

final class CreateAkismetRequestCommandHandler implements MessageHandlerInterface
{
    private AkismetConfigurationRepositoryInterface $configurationRepository;
    private AkismetRequestRepositoryInterface $requestRepository;
    private AkismetApiInterface $api;

    public function __construct(
        AkismetConfigurationRepositoryInterface $configurationRepository,
        AkismetRequestRepositoryInterface $requestRepository,
        AkismetApiInterface $api
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->requestRepository = $requestRepository;
        $this->api = $api;
    }

    public function __invoke(CreateAkismetRequestCommand $command): bool
    {
        $formId = $command->getFormId();
        $params = $command->getParams();

        $akismetConfiguration = $this->configurationRepository->getByFormId($formId);
        $params['blog'] = $akismetConfiguration->getSiteUrl();

        $result = $this->api->checkComment($akismetConfiguration, $params);

        switch ($result) {
            case AkismetApiInterface::RESULT_HAM:
                $spam = false;
                break;
            case AkismetApiInterface::RESULT_SPAM:
            case AkismetApiInterface::RESULT_DISCARD:
                $spam = true;
                break;
            default:
                throw new \LogicException();
        }

        $akismetRequest = $this->requestRepository->create($akismetConfiguration, $params, $spam);
        $this->requestRepository->add($akismetRequest);

        return $spam;
    }
}
