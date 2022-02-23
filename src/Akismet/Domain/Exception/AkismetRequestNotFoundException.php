<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception;

use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

final class AkismetRequestNotFoundException extends ModelNotFoundException implements UnrecoverableExceptionInterface
{
    public static function getModelName(): string
    {
        return 'AkismetRequest';
    }
}
