<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\UserInterface\Controller\Admin;

use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation\DoctrineListRepresentationFactoryInterface;
use HandcraftedInTheAlps\Bundle\SuluResourceBundle\MessageBus\HandleTrait;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetRequestCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsHamCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsSpamCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Webmozart\Assert\Assert;

/**
 * @RouteResource("akismet-request")
 */
final class AkismetRequestController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use HandleTrait;

    private DoctrineListRepresentationFactoryInterface $doctrineListRepresentationFactory;

    public function __construct(
        DoctrineListRepresentationFactoryInterface $doctrineListRepresentationFactory,
        MessageBusInterface $messageBus,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->messageBus = $messageBus;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $formId = $request->query->getInt('formId');
        Assert::notEmpty($formId);

        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            AkismetRequestInterface::RESOURCE_KEY,
            ['formId' => $formId],
            [],
            ['requestParams'],
        );

        $data = array_map(
            function ($item): array {
                /** @var array<string, mixed> $result */
                $result = $item;
                /** @var array<string, mixed> $requestParams */
                $requestParams = $result['requestParams'];
                /** @var string|null $honeypotFieldName */
                $honeypotFieldName = null;

                foreach ($requestParams as $key => $value) {
                    $result['_'.$key] = $value;

                    if ('honeypot_field_name' === $key) {
                        $honeypotFieldName = $value;
                    }
                }

                if ($honeypotFieldName) {
                    $result['_honeypot_field_value'] = $requestParams[$honeypotFieldName] ?? null;
                    unset($result['_'.$honeypotFieldName]);
                }

                unset($result['requestParams']);

                return $result;
            },
            $listRepresentation->getData()
        );

        $listRepresentation = new PaginatedRepresentation(
            $data,
            $listRepresentation->getRel(),
            $listRepresentation->getPage(),
            $listRepresentation->getLimit(),
            $listRepresentation->getTotal()
        );

        return $this->handleView(
            $this->view($listRepresentation)
        );
    }

    /**
     * This method just exists to generate the `akismet_request` detail route.
     */
    public function getAction(int $id): Response
    {
        return new Response('', Response::HTTP_NOT_FOUND);
    }

    public function postTriggerAction(Request $request, int $id): Response
    {
        $action = $request->query->get('action');
        Assert::stringNotEmpty($action);
        Assert::oneOf($action, ['markAsSpam', 'markAsHam']);

        switch ($action) {
            case 'markAsSpam':
                $command = new MarkAkismetRequestAsSpamCommand($id);
                break;
            case 'markAsHam':
                $command = new MarkAkismetRequestAsHamCommand($id);
                break;
            default:
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException();
                // @codeCoverageIgnoreEnd
        }

        /**
         * @see MarkAkismetRequestAsSpamCommandHandler::__invoke()
         * @see MarkAkismetRequestAsHamCommandHandler::__invoke()
         */
        $akismetRequest = $this->handle($command);
        Assert::isInstanceOf($akismetRequest, AkismetRequestInterface::class);

        return $this->handleView(
            $this->view($akismetRequest)
        );
    }

    public function deleteAction(int $id): Response
    {
        /* @see DeleteAkismetRequestCommandHandler::__invoke() */
        $this->handle(new DeleteAkismetRequestCommand($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function getSecurityContext(): string
    {
        return AkismetRequestInterface::SECURITY_CONTEXT;
    }
}
