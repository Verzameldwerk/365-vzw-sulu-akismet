<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsSpamCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

final class MarkAkismetRequestAsSpamCommandHandler implements MessageHandlerInterface
{
    private AkismetRequestRepositoryInterface $repository;
    private AkismetApiInterface $api;

    public function __construct(
        AkismetRequestRepositoryInterface $repository,
        AkismetApiInterface $api
    ) {
        $this->repository = $repository;
        $this->api = $api;
    }

    public function __invoke(MarkAkismetRequestAsSpamCommand $command): AkismetRequestInterface
    {
        $id = $command->getId();

        $akismetRequest = $this->repository->getById($id);
        $akismetRequest->setSpam(true);

        $this->api->submitSpam(
            $akismetRequest->getAkismetConfiguration(),
            $akismetRequest->getRequestParams()
        );

        return $akismetRequest;
    }
}
