<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\CreateAkismetConfigurationCommandHandler;

/**
 * @see CreateAkismetConfigurationCommandHandler::__invoke()
 */
final class CreateAkismetConfigurationCommand implements SynchronousCommandInterface
{
    private int $formId;

    /**
     * @var mixed[]
     */
    private array $data;

    /**
     * @param mixed[] $data
     */
    public function __construct(int $formId, array $data)
    {
        $this->formId = $formId;
        $this->data = $data;
    }

    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}
