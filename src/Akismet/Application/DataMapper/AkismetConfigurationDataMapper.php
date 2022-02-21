<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\FormBundle\Entity\FormField;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Webmozart\Assert\Assert;

final class AkismetConfigurationDataMapper implements AkismetConfigurationDataMapperInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function mapData(AkismetConfigurationInterface $akismetConfiguration, array $data): void
    {
        $akismetConfiguration->setSiteUrl($this->getSiteUrl($data));
        $akismetConfiguration->setApiKey($this->getApiKey($data));
        $akismetConfiguration->setAuthorNameField($this->getFormField('authorNameField', $data));
        $akismetConfiguration->setAuthorEmailField($this->getFormField('authorEmailField', $data));
        $akismetConfiguration->setContentField($this->getFormField('contentField', $data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function getSiteUrl(array $data): ?string
    {
        Assert::keyExists($data, 'siteUrl');

        $siteUrl = $data['siteUrl'];
        Assert::nullOrString($siteUrl);

        return $siteUrl;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function getApiKey(array $data): ?string
    {
        Assert::keyExists($data, 'apiKey');

        $apiKey = $data['apiKey'];
        Assert::nullOrString($apiKey);

        return $apiKey;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function getFormField(string $fieldName, array $data): ?FormField
    {
        Assert::keyExists($data, $fieldName);

        $id = $data[$fieldName];
        Assert::nullOrInteger($id);

        if (null !== $id) {
            return $this->entityManager->getReference(FormField::class, $id);
        }

        return null;
    }
}
