<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model;

use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

class AkismetRequest implements AkismetRequestInterface, AuditableInterface
{
    use AuditableTrait;

    private ?int $id = null;
    private AkismetConfigurationInterface $akismetConfiguration;

    /**
     * @var array<string, mixed>
     */
    private array $requestParams;
    private bool $spam;

    /**
     * @param array<string, mixed> $requestParams
     */
    public function __construct(
        AkismetConfigurationInterface $akismetConfiguration,
        array $requestParams,
        bool $spam
    ) {
        $this->akismetConfiguration = $akismetConfiguration;
        $this->requestParams = $requestParams;
        $this->spam = $spam;
    }

    public function getAkismetConfiguration(): AkismetConfigurationInterface
    {
        return $this->akismetConfiguration;
    }

    public function getRequestParams(): array
    {
        return $this->requestParams;
    }

    public function isSpam(): bool
    {
        return $this->spam;
    }

    public function setSpam(bool $spam): void
    {
        $this->spam = $spam;
    }
}
