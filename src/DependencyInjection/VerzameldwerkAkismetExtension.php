<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\DependencyInjection;

use HandcraftedInTheAlps\Bundle\SuluResourceBundle\MessageBus\RegisterMessageBusTrait;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\UserInterface\Controller\Admin\AkismetFormFieldController;

final class VerzameldwerkAkismetExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;
    use RegisterMessageBusTrait;

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig(
                'doctrine',
                [
                    'orm' => [
                        'mappings' => [
                            'VerzameldwerkAkismetBundle' => [
                                'type' => 'xml',
                                'dir' => __DIR__.'/../../config/doctrine',
                                'prefix' => 'Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model',
                                'alias' => 'VerzameldwerkAkismetBundle',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'forms' => [
                        'directories' => [
                            __DIR__.'/../../config/forms',
                        ],
                    ],
                    'lists' => [
                        'directories' => [
                            __DIR__.'/../../config/lists',
                        ],
                    ],
                    'resources' => [
                        AkismetConfigurationInterface::RESOURCE_KEY => [
                            'routes' => [
                                'list' => 'verzameldwerk_akismet.cget_akismet-configuration',
                                'detail' => 'verzameldwerk_akismet.get_akismet-configuration',
                            ],
                        ],
                        AkismetRequestInterface::RESOURCE_KEY => [
                            'routes' => [
                                'list' => 'verzameldwerk_akismet.get_akismet-requests',
                                'detail' => 'verzameldwerk_akismet.get_akismet-request',
                            ],
                        ],
                        AkismetFormFieldController::RESOURCE_KEY => [
                            'routes' => [
                                'list' => 'verzameldwerk_akismet.get_akismet-form-fields',
                                'detail' => 'verzameldwerk_akismet.get_akismet-form-field',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'single_selection' => [
                            'single_form_field_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => AkismetFormFieldController::RESOURCE_KEY,
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => AkismetFormFieldController::LIST_KEY,
                                        'display_properties' => ['title'],
                                        'empty_text' => 'verzameldwerk_akismet.no_form_field_selected',
                                        'icon' => 'su-tree-list',
                                        'overlay_title' => 'sulu_form.select_form_field',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('fos_rest')) {
            $container->prependExtensionConfig(
                'fos_rest',
                [
                    'exception' => [
                        'codes' => [
                            ModelNotFoundException::class => 404,
                        ],
                    ],
                ]
            );
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (!\array_key_exists('SuluFormBundle', $bundles)) {
            throw new \LogicException('The VerzameldwerkAkismetBundle requires the SuluFormBundle to be enabled');
        }

        if (!\array_key_exists('HandcraftedInTheAlpsSuluResourceBundle', $bundles)) {
            throw new \LogicException('The VerzameldwerkAkismetBundle requires the HandcraftedInTheAlpsSuluResourceBundle to be enabled');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->configurePersistence($config['objects'], $container);
        $this->loadServices($container);
        $this->registerMessageBusWithFlushMiddleware($container, 'verzameldwerk_akismet.command_bus');
    }

    private function loadServices(ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('admin.xml');
        $loader->load('api.xml');
        $loader->load('command_handlers.xml');
        $loader->load('controllers.xml');
        $loader->load('data_mappers.xml');
        $loader->load('form.xml');
        $loader->load('repositories.xml');
        $loader->load('resolvers.xml');
    }
}
