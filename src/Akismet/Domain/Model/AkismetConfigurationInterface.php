<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model;

use Sulu\Bundle\FormBundle\Entity\FormField;

interface AkismetConfigurationInterface
{
    public const RESOURCE_KEY = 'akismet_configurations';
    public const FORM_KEY = 'akismet_configuration';
    public const SECURITY_CONTEXT = 'sulu.akismet.akismet_configurations';

    public function isActive(): bool;

    public function setActive(bool $active): void;

    public function getSiteUrl(): ?string;

    public function setSiteUrl(?string $siteUrl): void;

    public function getApiKey(): ?string;

    public function setApiKey(?string $apiKey): void;

    public function getAuthorNameField(): ?FormField;

    public function setAuthorNameField(?FormField $authorNameField): void;

    public function getAuthorEmailField(): ?FormField;

    public function setAuthorEmailField(?FormField $authorEmailField): void;

    public function getContentField(): ?FormField;

    public function setContentField(?FormField $contentField): void;
}
