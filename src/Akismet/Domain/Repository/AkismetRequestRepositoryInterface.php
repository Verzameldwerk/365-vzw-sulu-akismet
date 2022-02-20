<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetRequestNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;

interface AkismetRequestRepositoryInterface
{
    /**
     * @param array<string, mixed> $requestParams
     */
    public function create(AkismetConfigurationInterface $akismetConfiguration, array $requestParams, bool $spam): AkismetRequestInterface;

    public function add(AkismetRequestInterface $akismetRequest): void;

    public function remove(AkismetRequestInterface $akismetRequest): void;

    /**
     * @throws AkismetRequestNotFoundException
     */
    public function getById(int $id): AkismetRequestInterface;
}
