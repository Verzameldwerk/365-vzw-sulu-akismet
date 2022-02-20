<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetRequestNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequest;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

final class AkismetRequestRepository implements AkismetRequestRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<AkismetRequestInterface>
     */
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(AkismetRequestInterface::class);
    }

    public function create(AkismetConfigurationInterface $akismetConfiguration, array $requestParams, bool $spam): AkismetRequestInterface
    {
        return new AkismetRequest($akismetConfiguration, $requestParams, $spam);
    }

    public function add(AkismetRequestInterface $akismetRequest): void
    {
        $this->entityManager->persist($akismetRequest);
    }

    public function remove(AkismetRequestInterface $akismetRequest): void
    {
        $this->entityManager->remove($akismetRequest);
    }

    public function getById(int $id): AkismetRequestInterface
    {
        $akismetRequest = $this->entityRepository->find($id);

        if (!$akismetRequest instanceof AkismetRequestInterface) {
            throw new AkismetRequestNotFoundException(['id' => $id]);
        }

        return $akismetRequest;
    }
}
