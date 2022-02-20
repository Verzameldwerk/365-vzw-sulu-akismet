<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\CreateAkismetRequestCommandHandler;

/**
 * @see CreateAkismetRequestCommandHandler::__invoke()
 */
final class CreateAkismetRequestCommand implements AsynchronousMessageInterface
{
    private int $formId;

    /**
     * @var array<string, mixed>
     */
    private array $params;

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(int $formId, array $params)
    {
        $this->formId = $formId;
        $this->params = $params;
    }

    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
