<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sulu\Bundle\FormBundle\Entity\Form;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class AkismetConfigurationRepository implements AkismetConfigurationRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<AkismetConfigurationInterface>
     */
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(AkismetConfigurationInterface::class);
    }

    public function create(int $formId): AkismetConfigurationInterface
    {
        /** @var Form $form */
        $form = $this->entityManager->getReference(Form::class, $formId);

        return new AkismetConfiguration($form);
    }

    public function add(AkismetConfigurationInterface $akismetConfiguration): void
    {
        $this->entityManager->persist($akismetConfiguration);
    }

    public function remove(AkismetConfigurationInterface $akismetConfiguration): void
    {
        $this->entityManager->remove($akismetConfiguration);
    }

    public function getById(int $id): AkismetConfigurationInterface
    {
        $akismetConfiguration = $this->entityRepository->find($id);

        if (!$akismetConfiguration instanceof AkismetConfigurationInterface) {
            throw new AkismetConfigurationNotFoundException(['id' => $id]);
        }

        return $akismetConfiguration;
    }

    public function getByFormId(int $formId): AkismetConfigurationInterface
    {
        $criteria = ['form' => $formId];
        $akismetConfiguration = $this->entityRepository->findOneBy($criteria);

        if (!$akismetConfiguration instanceof AkismetConfigurationInterface) {
            throw new AkismetConfigurationNotFoundException($criteria);
        }

        return $akismetConfiguration;
    }
}
