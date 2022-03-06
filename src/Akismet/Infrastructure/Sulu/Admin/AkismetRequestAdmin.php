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
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;

final class AkismetRequestAdmin extends Admin
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
        if (!$this->securityChecker->hasPermission(AkismetRequestInterface::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $toolbarActions = [];

        if ($this->securityChecker->hasPermission(AkismetRequestInterface::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $toolbarActions[] = new ToolbarAction('verzameldwerk_akismet.trigger', [
                'label' => 'verzameldwerk_akismet.mark_as_spam',
                'icon' => 'su-ban',
                'action' => 'markAsSpam',
            ]);

            $toolbarActions[] = new ToolbarAction('verzameldwerk_akismet.trigger', [
                'label' => 'verzameldwerk_akismet.mark_as_ham',
                'icon' => 'su-check-circle',
                'action' => 'markAsHam',
            ]);
        }

        if ($this->securityChecker->hasPermission(AkismetRequestInterface::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $toolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($viewCollection->has(FormAdmin::EDIT_FORM_VIEW)) {
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createListViewBuilder(FormAdmin::EDIT_FORM_VIEW.'.akismet_requests', '/akismet-requests')
                    ->setResourceKey(AkismetRequestInterface::RESOURCE_KEY)
                    ->setListKey(AkismetRequestInterface::LIST_KEY)
                    ->addListAdapters(['table'])
                    ->setTabTitle('verzameldwerk_akismet.akismet_requests')
                    ->setTabOrder(1025)
                    ->setTitle('verzameldwerk_akismet.akismet_requests')
                    ->addRouterAttributesToListRequest(['id' => 'formId'])
                    ->addToolbarActions($toolbarActions)
                    ->setParent(FormAdmin::EDIT_FORM_VIEW)
            );
        }
    }

    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Akismet' => [
                    AkismetRequestInterface::SECURITY_CONTEXT => [
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
