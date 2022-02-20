<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

interface AkismetConfigurationDataMapperInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function mapData(AkismetConfigurationInterface $akismetConfiguration, array $data): void;
}
