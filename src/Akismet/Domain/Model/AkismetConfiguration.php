<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model;

use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

class AkismetConfiguration implements AkismetConfigurationInterface, AuditableInterface
{
    use AuditableTrait;

    private ?int $id = null;
    private Form $form;
    private bool $active = false;
    private ?string $siteUrl = null;
    private ?string $apiKey = null;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getSiteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function setSiteUrl(?string $siteUrl): void
    {
        $this->siteUrl = $siteUrl;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
