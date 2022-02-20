<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle;

use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\DependencyInjection\VerzameldwerkAkismetExtension;

final class VerzameldwerkAkismetBundle extends Bundle
{
    use PersistenceBundleTrait;

    public function build(ContainerBuilder $container): void
    {
        $this->buildPersistence(
            [
                AkismetConfigurationInterface::class => 'sulu.model.akismet_configuration.class',
                AkismetRequestInterface::class => 'sulu.model.akismet_request.class',
            ],
            $container
        );
    }

    public function getContainerExtension(): VerzameldwerkAkismetExtension
    {
        return new VerzameldwerkAkismetExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
