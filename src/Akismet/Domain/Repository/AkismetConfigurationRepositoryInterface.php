<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

interface AkismetConfigurationRepositoryInterface
{
    public function create(int $formId): AkismetConfigurationInterface;

    public function add(AkismetConfigurationInterface $akismetConfiguration): void;

    public function remove(AkismetConfigurationInterface $akismetConfiguration): void;

    /**
     * @throws AkismetConfigurationNotFoundException
     */
    public function getById(int $id): AkismetConfigurationInterface;

    /**
     * @throws AkismetConfigurationNotFoundException
     */
    public function getByFormId(int $formId): AkismetConfigurationInterface;
}
