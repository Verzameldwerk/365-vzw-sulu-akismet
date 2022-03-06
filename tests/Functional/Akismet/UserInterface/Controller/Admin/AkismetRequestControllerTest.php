<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Functional\Akismet\UserInterface\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\TestBundle\Testing\SetGetPrivatePropertyTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequest;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\UserInterface\Controller\Admin\AkismetRequestController
 */
class AkismetRequestControllerTest extends SuluTestCase
{
    use SetGetPrivatePropertyTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = $this->createAuthenticatedClient();
        $this->entityManager = self::getEntityManager();
    }

    public function testCGetAction(): void
    {
        $form = $this->createForm();
        $akismetConfiguration = $this->createAkismetConfiguration(['form' => $form]);
        $date = new \DateTime();
        $request1 = $this->createAkismetRequest(['akismetConfiguration' => $akismetConfiguration, 'date' => $date]);
        $request2 = $this->createAkismetRequest(['akismetConfiguration' => $akismetConfiguration, 'date' => $date]);
        $request3 = $this->createAkismetRequest(['akismetConfiguration' => $akismetConfiguration, 'date' => $date]);
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();
        /** @var int $akismetConfigurationId */
        $akismetConfigurationId = $this->getPrivateProperty($akismetConfiguration, 'id');
        $request1Id = $this->getPrivateProperty($request1, 'id');
        $request2Id = $this->getPrivateProperty($request2, 'id');
        $request3Id = $this->getPrivateProperty($request3, 'id');
        $this->entityManager->clear();

        $akismetRequests = $this->entityManager->getRepository(AkismetRequestInterface::class)
            ->findBy(['akismetConfiguration' => $akismetConfigurationId]);

        self::assertCount(3, $akismetRequests);

        $this->client->jsonRequest('GET', '/admin/api/akismet-requests?locale=en&formId='.$formId);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $result = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(
            [
                '_embedded' => [
                    'akismet_requests' => [
                        [
                            'id' => $request1Id,
                            'spam' => true,
                            'changer' => null,
                            'changed' => $date->format('Y-m-d\TH:i:s'),
                            'created' => $date->format('Y-m-d\TH:i:s'),
                            '_foo' => 'bar',
                            '_honeypot_field_name' => 'honey',
                            '_honeypot_field_value' => 'pot',
                        ],
                        [
                            'id' => $request2Id,
                            'spam' => true,
                            'changer' => null,
                            'changed' => $date->format('Y-m-d\TH:i:s'),
                            'created' => $date->format('Y-m-d\TH:i:s'),
                            '_foo' => 'bar',
                            '_honeypot_field_name' => 'honey',
                            '_honeypot_field_value' => 'pot',
                        ],
                        [
                            'id' => $request3Id,
                            'spam' => true,
                            'changer' => null,
                            'changed' => $date->format('Y-m-d\TH:i:s'),
                            'created' => $date->format('Y-m-d\TH:i:s'),
                            '_foo' => 'bar',
                            '_honeypot_field_name' => 'honey',
                            '_honeypot_field_value' => 'pot',
                        ],
                    ],
                ],
                'limit' => 10,
                'total' => 3,
                'page' => 1,
                'pages' => 1,
            ],
            $result
        );
    }

    public function testGetAction(): void
    {
        $this->client->jsonRequest('GET', '/admin/api/akismet-requests/1');
        $this->assertHttpStatusCode(404, $this->client->getResponse());
    }

    public function testPostTriggerActionMarkAsHam(): void
    {
        $akismetRequest = $this->createAkismetRequest(['spam' => true]);
        $this->entityManager->flush();
        /** @var int $id */
        $id = $this->getPrivateProperty($akismetRequest, 'id');
        $this->entityManager->clear();

        $akismetRequest = $this->entityManager->getRepository(AkismetRequestInterface::class)->find($id);
        self::assertInstanceOf(AkismetRequestInterface::class, $akismetRequest);
        self::assertTrue($akismetRequest->isSpam());

        $this->client->jsonRequest('POST', '/admin/api/akismet-requests/'.$id.'?action=markAsHam');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $akismetRequest = $this->entityManager->getRepository(AkismetRequestInterface::class)->find($id);
        self::assertInstanceOf(AkismetRequestInterface::class, $akismetRequest);
        self::assertFalse($akismetRequest->isSpam());
    }

    public function testPostTriggerActionMarkAsSpam(): void
    {
        $akismetRequest = $this->createAkismetRequest(['spam' => false]);
        $this->entityManager->flush();
        /** @var int $id */
        $id = $this->getPrivateProperty($akismetRequest, 'id');
        $this->entityManager->clear();

        $akismetRequest = $this->entityManager->getRepository(AkismetRequestInterface::class)->find($id);
        self::assertInstanceOf(AkismetRequestInterface::class, $akismetRequest);
        self::assertFalse($akismetRequest->isSpam());

        $this->client->jsonRequest('POST', '/admin/api/akismet-requests/'.$id.'?action=markAsSpam');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $akismetRequest = $this->entityManager->getRepository(AkismetRequestInterface::class)->find($id);
        self::assertInstanceOf(AkismetRequestInterface::class, $akismetRequest);
        self::assertTrue($akismetRequest->isSpam());
    }

    public function testDeleteAction(): void
    {
        $akismetRequest = $this->createAkismetRequest();
        $this->entityManager->flush();
        /** @var int $id */
        $id = $this->getPrivateProperty($akismetRequest, 'id');
        $this->entityManager->clear();

        $akismetRequest = $this->entityManager->getRepository(AkismetRequestInterface::class)->find($id);
        self::assertInstanceOf(AkismetRequestInterface::class, $akismetRequest);

        $this->client->jsonRequest('DELETE', '/admin/api/akismet-requests/'.$id);
        $this->assertHttpStatusCode(204, $this->client->getResponse());

        $akismetRequest = $this->entityManager->getRepository(AkismetRequestInterface::class)->find($id);
        self::assertNull($akismetRequest);
    }

    /**
     * @param array{
     *     akismetConfiguration?: AkismetConfigurationInterface,
     *     date?: \DateTime,
     *     spam?: bool,
     * } $data
     */
    private function createAkismetRequest(array $data = []): AkismetRequestInterface
    {
        $akismetRequest = new AkismetRequest(
            $data['akismetConfiguration'] ?? $this->createAkismetConfiguration(),
            ['foo' => 'bar', 'honeypot_field_name' => 'honey', 'honey' => 'pot'],
            true
        );
        $this->entityManager->persist($akismetRequest);

        if (isset($data['date'])) {
            $akismetRequest->setCreated($data['date']);
            $akismetRequest->setChanged($data['date']);
        }

        if (isset($data['spam'])) {
            $akismetRequest->setSpam($data['spam']);
        }

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
