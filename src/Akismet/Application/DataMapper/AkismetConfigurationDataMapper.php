<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Webmozart\Assert\Assert;

final class AkismetConfigurationDataMapper implements AkismetConfigurationDataMapperInterface
{
    public function mapData(AkismetConfigurationInterface $akismetConfiguration, array $data): void
    {
        $akismetConfiguration->setSiteUrl($this->getSiteUrl($data));
        $akismetConfiguration->setApiKey($this->getApiKey($data));
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
}
