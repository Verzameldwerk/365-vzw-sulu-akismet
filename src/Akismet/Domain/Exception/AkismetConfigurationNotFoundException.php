<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception;

use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

final class AkismetConfigurationNotFoundException extends ModelNotFoundException implements UnrecoverableExceptionInterface
{
    protected static function getModelName(): string
    {
        return 'AkismetConfiguration';
    }
}
