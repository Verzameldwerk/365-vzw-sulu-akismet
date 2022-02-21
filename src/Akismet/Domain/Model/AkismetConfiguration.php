<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\FormBundle\Entity\FormField;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class AkismetConfiguration implements AkismetConfigurationInterface, AuditableInterface
{
    use AuditableTrait;

    /**
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @Serializer\Expose()
     */
    private bool $active = false;

    /**
     * @Serializer\Expose()
     */
    private ?string $siteUrl = null;

    /**
     * @Serializer\Expose()
     */
    private ?string $apiKey = null;

    private Form $form;
    private ?FormField $authorNameField = null;
    private ?FormField $authorEmailField = null;
    private ?FormField $contentField = null;

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

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("formId")
     */
    public function getFormId(): ?int
    {
        return $this->form->getId();
    }

    public function getAuthorNameField(): ?FormField
    {
        return $this->authorNameField;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("authorNameField")
     */
    public function getAuthorNameFieldId(): ?int
    {
        return $this->authorNameField ? $this->authorNameField->getId() : null;
    }

    public function setAuthorNameField(?FormField $authorNameField): void
    {
        $this->authorNameField = $authorNameField;
    }

    public function getAuthorEmailField(): ?FormField
    {
        return $this->authorEmailField;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("authorEmailField")
     */
    public function getAuthorEmailFieldId(): ?int
    {
        return $this->authorEmailField ? $this->authorEmailField->getId() : null;
    }

    public function setAuthorEmailField(?FormField $authorEmailField): void
    {
        $this->authorEmailField = $authorEmailField;
    }

    public function getContentField(): ?FormField
    {
        return $this->contentField;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("contentField")
     */
    public function getContentFieldId(): ?int
    {
        return $this->contentField ? $this->contentField->getId() : null;
    }

    public function setContentField(?FormField $contentField): void
    {
        $this->contentField = $contentField;
    }
}
