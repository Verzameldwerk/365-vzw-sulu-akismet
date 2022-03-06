<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Application;

use HandcraftedInTheAlps\Bundle\SuluResourceBundle\HandcraftedInTheAlpsSuluResourceBundle;
use Sulu\Bundle\AudienceTargetingBundle\SuluAudienceTargetingBundle;
use Sulu\Bundle\FormBundle\SuluFormBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Verzameldwerk\Bundle\AkismetBundle\VerzameldwerkAkismetBundle;

class Kernel extends SuluTestKernel
{
    public function registerBundles(): iterable
    {
        $bundles = parent::registerBundles();
        $bundles[] = new SuluFormBundle();
        $bundles[] = new HandcraftedInTheAlpsSuluResourceBundle();
        $bundles[] = new VerzameldwerkAkismetBundle();

        foreach ($bundles as $key => $bundle) {
            // Audience Targeting is not configured and so should not be here
            if ($bundle instanceof SuluAudienceTargetingBundle) {
                unset($bundles[$key]);

                break;
            }
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__.'/config/config_'.$this->getContext().'.yml');

        if ('test' === $this->getEnvironment()) {
            $loader->load(__DIR__.'/config/services_test.yml');
        }
    }
}
