<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Verzameldwerk\Bundle\AkismetBundle\Tests\Application\Kernel;

require dirname(__DIR__).'/Application/config/bootstrap.php';

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG'], Kernel::CONTEXT_ADMIN);
$kernel->boot();

$container = $kernel->getContainer();

/** @var EntityManager $objectManager */
$objectManager = $container->get('doctrine')->getManager();

// remove ResolveTargetEntityListener from returned EntityManager to not resolve SuluPersistenceBundle classes
// this is a workaround for the following phpstan issue: https://github.com/phpstan/phpstan-doctrine/issues/98
$resolveTargetEntityListener = current(array_filter(
    $objectManager->getEventManager()->getListeners('loadClassMetadata'),
    static function ($listener) {
        return $listener instanceof ResolveTargetEntityListener;
    }
));
if ($resolveTargetEntityListener) {
    $objectManager->getEventManager()->removeEventListener([Events::loadClassMetadata], $resolveTargetEntityListener);
}

return $objectManager;
