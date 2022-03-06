<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Sulu\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\FormBundle\Admin\FormAdmin;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

final class AkismetConfigurationAdmin extends Admin
{
    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if (!$this->securityChecker->hasPermission(AkismetConfigurationInterface::SECURITY_CONTEXT, PermissionTypes::VIEW)
            || !$this->securityChecker->hasPermission(AkismetConfigurationInterface::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $toolbarActions = [
            new ToolbarAction('sulu_admin.save'),
        ];

        if ($this->securityChecker->hasPermission(AkismetConfigurationInterface::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $toolbarActions[] = new ToolbarAction('sulu_admin.delete', [
                'router_attributes_to_back_view' => [
                    'id' => 'id',
                ],
            ]);
        }

        if ($viewCollection->has(FormAdmin::EDIT_FORM_VIEW)) {
            $viewCollection->add(
                // @phpstan-ignore-next-line
                $this->viewBuilderFactory
                    ->createFormViewBuilder(FormAdmin::EDIT_FORM_VIEW.'.akismet_configuration', '/akismet-configuration')
                    ->setResourceKey(AkismetConfigurationInterface::RESOURCE_KEY)
                    ->setFormKey(AkismetConfigurationInterface::FORM_KEY)
                    ->setTabTitle('verzameldwerk_akismet.akismet_configuration')
                    ->setTabOrder(1024)
                    ->setTitleVisible(true)
                    ->addToolbarActions($toolbarActions)
                    ->setBackView(FormAdmin::EDIT_FORM_VIEW)
                    ->setIdQueryParameter('formId')
                    ->addRouterAttributesToBackView(['id' => 'id'])
                    ->setParent(FormAdmin::EDIT_FORM_VIEW)
            );
        }
    }

    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Akismet' => [
                    AkismetConfigurationInterface::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }

    public static function getPriority(): int
    {
        return FormAdmin::getPriority() - 1;
    }
}
