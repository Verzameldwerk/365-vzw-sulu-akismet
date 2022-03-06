<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\UserInterface\Controller\Admin;

use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\Bundle\SuluResourceBundle\MessageBus\HandleTrait;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindOrCreateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeleteAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Webmozart\Assert\Assert;

/**
 * @RouteResource("akismet-configuration", pluralize=false)
 */
final class AkismetConfigurationController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $messageBus,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->messageBus = $messageBus;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $formId = $request->query->getInt('formId');
        Assert::notEmpty($formId);

        /* @see FindOrCreateAkismetConfigurationCommandHandler::__invoke() */
        $akismetConfiguration = $this->handle(
            new FindOrCreateAkismetConfigurationCommand($formId)
        );

        Assert::isInstanceOf($akismetConfiguration, AkismetConfigurationInterface::class);

        return $this->handleView(
            $this->view($akismetConfiguration)
        );
    }

    /**
     * This method just exists to generate the `akismet_configuration` detail route.
     */
    public function getAction(int $id): Response
    {
        return new Response('', Response::HTTP_NOT_FOUND);
    }

    public function putAction(Request $request, int $id): Response
    {
        /** @see UpdateAkismetConfigurationCommandHandler::__invoke() */
        $akismetConfiguration = $this->handle(
            new UpdateAkismetConfigurationCommand($id, $request->request->all())
        );

        Assert::isInstanceOf($akismetConfiguration, AkismetConfigurationInterface::class);

        return $this->handleView(
            $this->view($akismetConfiguration)
        );
    }

    public function deleteAction(int $id): Response
    {
        /* @see DeleteAkismetConfigurationCommandHandler::__invoke() */
        $this->handle(new DeleteAkismetConfigurationCommand($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function getSecurityContext(): string
    {
        return AkismetConfigurationInterface::SECURITY_CONTEXT;
    }
}
