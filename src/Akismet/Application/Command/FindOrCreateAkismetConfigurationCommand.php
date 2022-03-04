<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\FindOrCreateAkismetConfigurationCommandHandler;

/**
 * @see FindOrCreateAkismetConfigurationCommandHandler::__invoke()
 */
final class FindOrCreateAkismetConfigurationCommand implements SynchronousMessageInterface
{
    private int $formId;

    public function __construct(int $formId)
    {
        $this->formId = $formId;
    }

    public function getFormId(): int
    {
        return $this->formId;
    }
}
