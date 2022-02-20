<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\ActivateAkismetConfigurationCommandHandler;

/**
 * @see ActivateAkismetConfigurationCommandHandler::__invoke()
 */
final class ActivateAkismetConfigurationCommand implements SynchronousCommandInterface
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}