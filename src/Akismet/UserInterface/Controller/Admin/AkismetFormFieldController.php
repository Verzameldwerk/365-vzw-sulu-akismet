<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\UserInterface\Controller\Admin;

use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\Bundle\SuluResourceBundle\ListRepresentation\DoctrineListRepresentationFactoryInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * @RouteResource("akismet-form-field")
 */
final class AkismetFormFieldController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    public const RESOURCE_KEY = 'akismet_form_fields';
    public const LIST_KEY = 'akismet_form_fields';
    public const SECURITY_CONTEXT = 'sulu.form.forms';

    private DoctrineListRepresentationFactoryInterface $doctrineListRepresentationFactory;

    public function __construct(
        DoctrineListRepresentationFactoryInterface $doctrineListRepresentationFactory,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $formId = $request->query->getInt('formId');
        Assert::notEmpty($formId);

        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            self::RESOURCE_KEY,
            ['formId' => $formId],
            ['locale' => $request->getLocale()]
        );

        return $this->handleView(
            $this->view($listRepresentation)
        );
    }

    public function getAction(Request $request, int $id): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            self::RESOURCE_KEY,
            ['id' => $id],
            ['locale' => $request->getLocale()]
        );

        $formFields = $listRepresentation->getData();
        Assert::minCount($formFields, 1);

        return $this->handleView(
            $this->view($formFields[0])
        );
    }

    public function getSecurityContext(): string
    {
        return self::SECURITY_CONTEXT;
    }
}
