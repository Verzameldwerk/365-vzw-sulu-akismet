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

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\UserInterface\Controller\Admin\AkismetFormFieldController
 */
class AkismetFormFieldControllerTest extends SuluTestCase
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
        $authorNameField = $this->createFormField($form, 'text');
        $authorEmailField = $this->createFormField($form, 'email');
        $contentField = $this->createFormField($form, 'textarea');
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();
        $authorNameFieldId = $authorNameField->getId();
        $authorEmailFieldId = $authorEmailField->getId();
        $contentFieldId = $contentField->getId();
        $this->entityManager->clear();

        $this->client->jsonRequest('GET', '/admin/api/akismet-form-fields?locale=en&formId='.$formId);
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $result = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(
            [
                '_embedded' => [
                    'akismet_form_fields' => [
                        [
                            'id' => $authorNameFieldId,
                            'title' => 'text',
                        ],
                        [
                            'id' => $authorEmailFieldId,
                            'title' => 'email',
                        ],
                        [
                            'id' => $contentFieldId,
                            'title' => 'textarea',
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
        $form = $this->createForm();
        $formField = $this->createFormField($form, 'text');
        $this->entityManager->flush();
        $formFieldId = $formField->getId();
        $this->entityManager->clear();

        $this->client->jsonRequest('GET', '/admin/api/akismet-form-fields/'.$formFieldId.'?locale=en');
        $this->assertHttpStatusCode(200, $this->client->getResponse());

        $result = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(
            [
                'id' => $formFieldId,
                'title' => 'text',
            ],
            $result
        );
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
        $translation->setTitle($type);
        $translation->setLocale($form->getDefaultLocale());
        $translation->setField($field);
        $field->addTranslation($translation);
        $form->addField($field);

        return $field;
    }
}
