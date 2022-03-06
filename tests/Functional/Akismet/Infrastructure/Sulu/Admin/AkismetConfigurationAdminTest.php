<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Functional\Akismet\Infrastructure\Sulu\Admin;

use Sulu\Bundle\AdminBundle\Admin\View\ViewRegistry;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Sulu\Admin\AkismetConfigurationAdmin;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Sulu\Admin\AkismetConfigurationAdmin
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Sulu\Admin\AkismetRequestAdmin
 */
class AkismetConfigurationAdminTest extends SuluTestCase
{
    private ViewRegistry $viewRegistry;
    private AkismetConfigurationAdmin $admin;

    protected function setUp(): void
    {
        $this->purgeDatabase();

        $this->viewRegistry = self::getContainer()->get('sulu_admin.view_registry');
        $this->admin = self::getContainer()->get(AkismetConfigurationAdmin::class);
    }

    public function testConfigureViews(): void
    {
        $view = $this->viewRegistry->findViewByName('sulu_form.edit_form.akismet_configuration');

        self::assertSame('/forms/:locale/:id/akismet-configuration', $view->getPath());
        self::assertSame('sulu_admin.form', $view->getType());
        self::assertSame('sulu_form.edit_form', $view->getParent());
        self::assertSame('akismet_configurations', $view->getOption('resourceKey'));
        self::assertSame('akismet_configuration', $view->getOption('formKey'));
    }

    public function testGetSecurityContexts(): void
    {
        self::assertSame([
            'Sulu' => [
                'Akismet' => [
                    'sulu.akismet.akismet_configurations' => [
                        PermissionTypes::VIEW,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ], $this->admin->getSecurityContexts());
    }

    public function testGetPriority(): void
    {
        self::assertSame(-1, $this->admin::getPriority());
    }
}
