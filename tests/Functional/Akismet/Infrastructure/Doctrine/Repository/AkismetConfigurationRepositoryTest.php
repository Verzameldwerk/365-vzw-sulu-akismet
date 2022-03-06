<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Functional\Akismet\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\TestBundle\Testing\SetGetPrivatePropertyTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Doctrine\Repository\AkismetConfigurationRepository
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException
 */
class AkismetConfigurationRepositoryTest extends SuluTestCase
{
    use SetGetPrivatePropertyTrait;

    private EntityManagerInterface $entityManager;
    private AkismetConfigurationRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::purgeDatabase();

        $this->entityManager = self::getEntityManager();
        $this->repository = self::getContainer()->get('verzameldwerk_akismet.akismet_configuration_repository');
    }

    public function testCreate(): void
    {
        $form = $this->createForm();
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();
        $this->entityManager->clear();

        $akismetConfiguration = $this->repository->create($formId);

        self::assertNull($this->getPrivateProperty($akismetConfiguration, 'id'));
    }

    public function testAdd(): void
    {
        $akismetConfiguration = new AkismetConfiguration($this->createForm());
        $this->repository->add($akismetConfiguration);
        $this->entityManager->flush();
        $id = $this->getPrivateProperty($akismetConfiguration, 'id');
        $this->entityManager->clear();

        $akismetConfiguration = $this->entityManager->find(AkismetConfigurationInterface::class, $id);
        self::assertInstanceOf(AkismetConfigurationInterface::class, $akismetConfiguration);
    }

    public function testRemove(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $this->entityManager->flush();
        $id = $this->getPrivateProperty($akismetConfiguration, 'id');

        $this->repository->remove($akismetConfiguration);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $akismetConfiguration = $this->entityManager->find(AkismetConfigurationInterface::class, $id);

        self::assertNull($akismetConfiguration);
    }

    public function testGetById(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $this->entityManager->flush();
        /** @var int $id */
        $id = $this->getPrivateProperty($akismetConfiguration, 'id');
        $this->entityManager->clear();

        $akismetConfiguration = $this->repository->getById($id);

        self::assertSame($id, $this->getPrivateProperty($akismetConfiguration, 'id'));
    }

    public function testGetByIdNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getById(\PHP_INT_MAX);
    }

    public function testGetByFormId(): void
    {
        $form = $this->createForm();
        $akismetConfiguration = $this->createAkismetConfiguration(['form' => $form]);
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();
        $id = $this->getPrivateProperty($akismetConfiguration, 'id');
        $this->entityManager->clear();

        $akismetConfiguration = $this->repository->getByFormId($formId);

        self::assertSame($id, $this->getPrivateProperty($akismetConfiguration, 'id'));
    }

    public function testGetByFormIdNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getByFormId(\PHP_INT_MAX);
    }

    /**
     * @param array{
     *     form?: Form,
     * } $data
     */
    private function createAkismetConfiguration(array $data = []): AkismetConfigurationInterface
    {
        $akismetConfiguration = new AkismetConfiguration($data['form'] ?? $this->createForm());
        $this->entityManager->persist($akismetConfiguration);

        return $akismetConfiguration;
    }

    /**
     * @param array{
     *     defaultLocale?: string,
     * } $data
     */
    private function createForm(array $data = []): Form
    {
        $form = new Form();
        $form->setDefaultLocale($data['defaultLocale'] ?? 'en');
        $this->entityManager->persist($form);

        return $form;
    }
}
