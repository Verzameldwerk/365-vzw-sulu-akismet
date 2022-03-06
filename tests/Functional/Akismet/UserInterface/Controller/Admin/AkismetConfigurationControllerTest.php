<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Functional\Akismet\UserInterface\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\FormBundle\Entity\FormField;
use Sulu\Bundle\FormBundle\Entity\FormFieldTranslation;
use Sulu\Bundle\TestBundle\Testing\SetGetPrivatePropertyTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\UserInterface\Controller\Admin\AkismetConfigurationController
 */
class AkismetConfigurationControllerTest extends SuluTestCase
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
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();
        $this->entityManager->clear();

        $akismetConfigurations = $this->entityManager->getRepository(AkismetConfigurationInterface::class)
            ->findBy(['form' => $formId]);

        self::assertCount(0, $akismetConfigurations);

        $this->client->jsonRequest('GET', '/admin/api/akismet-configuration?locale=en&formId='.$formId);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $akismetConfigurations = $this->entityManager->getRepository(AkismetConfigurationInterface::class)
            ->findBy(['form' => $formId]);

        self::assertCount(1, $akismetConfigurations);
        $akismetConfigurationId = $this->getPrivateProperty($akismetConfigurations[0], 'id');

        $this->client->jsonRequest('GET', '/admin/api/akismet-configuration?locale=en&formId='.$formId);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $akismetConfigurations = $this->entityManager->getRepository(AkismetConfigurationInterface::class)
            ->findBy(['form' => $formId]);

        self::assertCount(1, $akismetConfigurations);
        self::assertSame($akismetConfigurationId, $this->getPrivateProperty($akismetConfigurations[0], 'id'));
    }

    public function testGetAction(): void
    {
        $this->client->jsonRequest('GET', '/admin/api/akismet-configuration/1');
        $this->assertHttpStatusCode(404, $this->client->getResponse());
    }

    public function testPutAction(): void
    {
        $form = $this->createForm();
        $authorNameField = $this->createFormField($form, 'text');
        $authorEmailField = $this->createFormField($form, 'email');
        $contentField = $this->createFormField($form, 'textarea');
        $akismetConfiguration = $this->createAkismetConfiguration(['form' => $form]);
        $this->entityManager->flush();
        /** @var int $id */
        $id = $this->getPrivateProperty($akismetConfiguration, 'id');
        $authorNameFieldId = $authorNameField->getId();
        $authorEmailFieldId = $authorEmailField->getId();
        $contentFieldId = $contentField->getId();
        $this->entityManager->clear();

        $this->client->jsonRequest('PUT', '/admin/api/akismet-configuration/'.$id, [
            'active' => false,
            'apiKey' => null,
            'siteUrl' => null,
            'authorNameField' => null,
            'authorEmailField' => null,
            'contentField' => null,
        ]);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $akismetConfiguration = $this->entityManager->getRepository(AkismetConfigurationInterface::class)->find($id);
        self::assertInstanceOf(AkismetConfigurationInterface::class, $akismetConfiguration);
        self::assertFalse($akismetConfiguration->isActive());
        self::assertNull($akismetConfiguration->getApiKey());
        self::assertNull($akismetConfiguration->getSiteUrl());
        self::assertNull($akismetConfiguration->getAuthorNameField());
        self::assertNull($akismetConfiguration->getAuthorEmailField());
        self::assertNull($akismetConfiguration->getContentField());

        $this->client->jsonRequest('PUT', '/admin/api/akismet-configuration/'.$id, [
            'active' => true,
            'apiKey' => 'apiKey',
            'siteUrl' => 'siteUrl',
            'authorNameField' => $authorNameFieldId,
            'authorEmailField' => $authorEmailFieldId,
            'contentField' => $contentFieldId,
        ]);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $akismetConfiguration = $this->entityManager->getRepository(AkismetConfigurationInterface::class)->find($id);
        self::assertInstanceOf(AkismetConfigurationInterface::class, $akismetConfiguration);
        self::assertTrue($akismetConfiguration->isActive());
        self::assertSame('apiKey', $akismetConfiguration->getApiKey());
        self::assertSame('siteUrl', $akismetConfiguration->getSiteUrl());
        self::assertNotNull($akismetConfiguration->getAuthorNameField());
        self::assertSame($authorNameFieldId, $akismetConfiguration->getAuthorNameField()->getId());
        self::assertNotNull($akismetConfiguration->getAuthorEmailField());
        self::assertSame($authorEmailFieldId, $akismetConfiguration->getAuthorEmailField()->getId());
        self::assertNotNull($akismetConfiguration->getContentField());
        self::assertSame($contentFieldId, $akismetConfiguration->getContentField()->getId());
    }

    public function testDeleteAction(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $this->entityManager->flush();
        /** @var int $id */
        $id = $this->getPrivateProperty($akismetConfiguration, 'id');
        $this->entityManager->clear();

        $akismetConfiguration = $this->entityManager->getRepository(AkismetConfigurationInterface::class)->find($id);
        self::assertInstanceOf(AkismetConfigurationInterface::class, $akismetConfiguration);

        $this->client->jsonRequest('DELETE', '/admin/api/akismet-configuration/'.$id);
        $this->assertHttpStatusCode(204, $this->client->getResponse());

        $akismetConfiguration = $this->entityManager->getRepository(AkismetConfigurationInterface::class)->find($id);
        self::assertNull($akismetConfiguration);
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

    private function createFormField(Form $form, string $type): FormField
    {
        $field = new FormField();
        $field->setDefaultLocale($form->getDefaultLocale());
        $field->setKey($type);
        $field->setType($type);
        $field->setWidth('full');
        $field->setRequired(true);
        $field->setOrder(1);
        $field->setForm($form);

        $translation = new FormFieldTranslation();
        $translation->setLocale($form->getDefaultLocale());
        $translation->setField($field);
        $field->addTranslation($translation);
        $form->addField($field);

        return $field;
    }
}
