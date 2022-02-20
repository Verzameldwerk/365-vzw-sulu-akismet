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
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\ActivateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeactivateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\ActivateAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeleteAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @RouteResource("akismet-configuration", pluralize=false)
 */
final class AkismetConfigurationController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use HandleTrait;

    private AkismetConfigurationRepositoryInterface $repository;

    public function __construct(
        AkismetConfigurationRepositoryInterface $repository,
        MessageBusInterface $messageBus,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->repository = $repository;
        $this->messageBus = $messageBus;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $formId = $request->query->getInt('formId');
        Assert::notEmpty($formId);

        try {
            $result = $this->repository->getByFormId($formId);
        } catch (AkismetConfigurationNotFoundException $e) {
            $result = new \stdClass();
        }

        return $this->handleView(
            $this->view($result)
        );
    }

    /**
     * This method just exists to generate the `akismet_configuration` detail route.
     */
    public function getAction(int $id): Response
    {
        throw new \LogicException('Not implemented');
    }

    public function postAction(Request $request): Response
    {
        $formId = $request->query->getInt('formId');
        Assert::notEmpty($formId);

        /** @see CreateAkismetConfigurationCommandHandler::__invoke() */
        $akismetConfiguration = $this->handle(
            new CreateAkismetConfigurationCommand($formId, $request->request->all())
        );

        Assert::isInstanceOf($akismetConfiguration, AkismetConfigurationInterface::class);

        return $this->handleView(
            $this->view($akismetConfiguration)
        );
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

    public function postTriggerAction(Request $request, int $id): Response
    {
        $action = $request->query->get('action');
        Assert::stringNotEmpty($action);
        Assert::oneOf($action, ['activate', 'deactivate']);

        switch ($action) {
            case 'activate':
                $command = new ActivateAkismetConfigurationCommand($id);
                break;
            case 'deactivate':
                $command = new DeactivateAkismetConfigurationCommand($id);
                break;
            default:
                throw new \InvalidArgumentException();
        }

        /**
         * @see ActivateAkismetConfigurationCommandHandler::__invoke()
         * @see DeactivateAkismetConfigurationCommandHandler::__invoke()
         */
        $akismetConfiguration = $this->handle($command);
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
