<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model;

interface AkismetRequestInterface
{
    public const RESOURCE_KEY = 'akismet_requests';
    public const LIST_KEY = 'akismet_requests';
    public const SECURITY_CONTEXT = 'sulu.akismet.akismet_requests';

    public function getAkismetConfiguration(): AkismetConfigurationInterface;

    /**
     * @return array<string, mixed>
     */
    public function getRequestParams(): array;

    public function isSpam(): bool;

    public function setSpam(bool $spam): void;
}
