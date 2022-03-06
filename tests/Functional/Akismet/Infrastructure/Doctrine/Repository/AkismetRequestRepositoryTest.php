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
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequest;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Doctrine\Repository\AkismetRequestRepository
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequest
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetRequestNotFoundException
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException
 */
class AkismetRequestRepositoryTest extends SuluTestCase
{
    use SetGetPrivatePropertyTrait;

    private EntityManagerInterface $entityManager;
    private AkismetRequestRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::purgeDatabase();

        $this->entityManager = self::getEntityManager();
        $this->repository = self::getContainer()->get('verzameldwerk_akismet.akismet_request_repository');
    }

    public function testCreate(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $this->entityManager->flush();

        $akismetRequest = $this->repository->create($akismetConfiguration, ['foo' => 'bar'], true);

        self::assertSame($akismetConfiguration, $akismetRequest->getAkismetConfiguration());
        self::assertSame(['foo' => 'bar'], $akismetRequest->getRequestParams());
        self::assertTrue($akismetRequest->isSpam());
    }

    public function testAdd(): void
    {
        $akismetRequest = new AkismetRequest($this->createAkismetConfiguration(), ['foo' => 'bar'], true);
        $this->repository->add($akismetRequest);
        $this->entityManager->flush();
        $id = $this->getPrivateProperty($akismetRequest, 'id');
        $this->entityManager->clear();

        $akismetRequest = $this->entityManager->find(AkismetRequestInterface::class, $id);
        self::assertInstanceOf(AkismetRequestInterface::class, $akismetRequest);
    }

    public function testRemove(): void
    {
        $akismetRequest = $this->createAkismetRequest();
        $this->entityManager->flush();
        $id = $this->getPrivateProperty($akismetRequest, 'id');

        $this->repository->remove($akismetRequest);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $akismetRequest = $this->entityManager->find(AkismetRequestInterface::class, $id);

        self::assertNull($akismetRequest);
    }

    public function testGetById(): void
    {
        $akismetRequest = $this->createAkismetRequest();
        $this->entityManager->flush();
        /** @var int $id */
        $id = $this->getPrivateProperty($akismetRequest, 'id');
        $this->entityManager->clear();

        $akismetRequest = $this->repository->getById($id);

        self::assertSame($id, $this->getPrivateProperty($akismetRequest, 'id'));
    }

    public function testGetByIdNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getById(\PHP_INT_MAX);
    }

    /**
     * @param array{
     *     akismetConfiguration?: AkismetConfigurationInterface,
     * } $data
     */
    private function createAkismetRequest(array $data = []): AkismetRequestInterface
    {
        $akismetRequest = new AkismetRequest(
            $data['akismetConfiguration'] ?? $this->createAkismetConfiguration(),
            ['foo' => 'bar'],
            true
        );
        $this->entityManager->persist($akismetRequest);

        return $akismetRequest;
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
