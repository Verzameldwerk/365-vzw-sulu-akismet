<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Functional\Akismet\Application\SpamChecker;

use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\FormBundle\Entity\FormField;
use Sulu\Bundle\FormBundle\Entity\FormFieldTranslation;
use Sulu\Bundle\FormBundle\Entity\FormTranslation;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Bundle\TestBundle\Testing\PurgeDatabaseTrait;
use Sulu\Bundle\TestBundle\Testing\SetGetPrivatePropertyTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\SpamCheckerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\AkismetSpamChecker
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Infrastructure\Sulu\Form\AkismetFormHandler
 */
class AkismetSpamCheckerTest extends SuluTestCase
{
    use ProphecyTrait;
    use PurgeDatabaseTrait;
    use SetGetPrivatePropertyTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private DocumentManagerInterface $documentManager;

    protected function setUp(): void
    {
        $this->client = static::createWebsiteClient();
        $this->entityManager = static::getEntityManager();
        $this->documentManager = static::getContainer()->get('sulu_document_manager.document_manager');
        $this->purgeDatabase();
        $this->initPhpcr();
    }

    public function testSubmitFormWithAkismetConfiguration(): void
    {
        $form = $this->createForm();
        $this->createFormField($form, 'email');
        $this->createFormField($form, 'firstName');
        $this->createFormField($form, 'lastName');
        $this->createFormField($form, 'textarea');
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();

        $akismetConfiguration = $this->createAkismetConfiguration(['form' => $form]);
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('apiKey');
        $akismetConfiguration->setSiteUrl('siteUrl');
        $this->entityManager->flush();
        $akismetConfigurationId = $this->getPrivateProperty($akismetConfiguration, 'id');

        $this->createPage('/test-page', $formId);
        $this->documentManager->flush();

        $crawler = $this->client->request('GET', '/test-page');
        $prefix = $crawler->filter('#sulu-form > form')->first()->attr('name');
        $this->client->submitForm('Submit', [
            $prefix.'[email]' => 'admin@example.com',
            $prefix.'[firstName]' => 'Adam',
            $prefix.'[lastName]' => 'Ministrator',
            $prefix.'[textarea]' => 'Lorem ipsum dolor sit amet',
        ]);

        $this->assertHttpStatusCode(302, $this->client->getResponse());

        /** @var AkismetRequestInterface[] $akismetRequests */
        $akismetRequests = $this->entityManager->getRepository(AkismetRequestInterface::class)->findBy(
            ['akismetConfiguration' => $akismetConfigurationId],
        );

        self::assertCount(1, $akismetRequests);
        self::assertFalse($akismetRequests[0]->isSpam());

        $requestParams = $akismetRequests[0]->getRequestParams();
        unset($requestParams['comment_date_gmt']);

        self::assertSame([
            'blog' => 'siteUrl',
            'user_ip' => '127.0.0.1',
            'referrer' => 'http://localhost/test-page',
            'blog_lang' => 'en',
            'permalink' => 'http://localhost/test-page',
            'user_agent' => 'Symfony BrowserKit',
            'comment_type' => 'contact-form',
            'comment_author' => 'Adam Ministrator',
            'honeypot_field' => null,
            'comment_content' => 'Lorem ipsum dolor sit amet',
            'honeypot_field_name' => 'honeypot_field',
            'comment_author_email' => 'admin@example.com',
        ], $requestParams);
    }

    public function testSubmitFormWithAkismetConfigurationIsSpam(): void
    {
        $form = $this->createForm();
        $this->createFormField($form, 'email');
        $this->createFormField($form, 'firstName');
        $this->createFormField($form, 'lastName');
        $this->createFormField($form, 'textarea');
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();

        $akismetConfiguration = $this->createAkismetConfiguration(['form' => $form]);
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('apiKey');
        $akismetConfiguration->setSiteUrl('siteUrl');
        $this->entityManager->flush();
        $akismetConfigurationId = $this->getPrivateProperty($akismetConfiguration, 'id');

        $this->createPage('/test-page', $formId);
        $this->documentManager->flush();

        $crawler = $this->client->request('GET', '/test-page');
        $prefix = $crawler->filter('#sulu-form > form')->first()->attr('name');
        $this->client->submitForm('Submit', [
            $prefix.'[email]' => 'admin@example.com',
            $prefix.'[firstName]' => 'Adam',
            $prefix.'[lastName]' => 'Ministrator',
            $prefix.'[textarea]' => SpamCheckerInterface::SPAM_STRATEGY_SPAM,
        ]);

        $this->assertHttpStatusCode(302, $this->client->getResponse());

        /** @var AkismetRequestInterface[] $akismetRequests */
        $akismetRequests = $this->entityManager->getRepository(AkismetRequestInterface::class)->findBy(
            ['akismetConfiguration' => $akismetConfigurationId],
        );

        self::assertCount(1, $akismetRequests);
        self::assertTrue($akismetRequests[0]->isSpam());

        $requestParams = $akismetRequests[0]->getRequestParams();
        unset($requestParams['comment_date_gmt']);

        self::assertSame([
            'blog' => 'siteUrl',
            'user_ip' => '127.0.0.1',
            'referrer' => 'http://localhost/test-page',
            'blog_lang' => 'en',
            'permalink' => 'http://localhost/test-page',
            'user_agent' => 'Symfony BrowserKit',
            'comment_type' => 'contact-form',
            'comment_author' => 'Adam Ministrator',
            'honeypot_field' => null,
            'comment_content' => 'spam',
            'honeypot_field_name' => 'honeypot_field',
            'comment_author_email' => 'admin@example.com',
        ], $requestParams);
    }

    public function testSubmitFormWithInactiveAkismetConfiguration(): void
    {
        $form = $this->createForm();
        $this->createFormField($form, 'email');
        $this->createFormField($form, 'firstName');
        $this->createFormField($form, 'lastName');
        $this->createFormField($form, 'textarea');
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();

        $akismetConfiguration = $this->createAkismetConfiguration(['form' => $form]);
        $akismetConfiguration->setActive(false);
        $akismetConfiguration->setApiKey('apiKey');
        $akismetConfiguration->setSiteUrl('siteUrl');
        $this->entityManager->flush();
        $akismetConfigurationId = $this->getPrivateProperty($akismetConfiguration, 'id');

        $this->createPage('/test-page', $formId);
        $this->documentManager->flush();

        $crawler = $this->client->request('GET', '/test-page');
        $prefix = $crawler->filter('#sulu-form > form')->first()->attr('name');
        $this->client->submitForm('Submit', [
            $prefix.'[email]' => 'admin@example.com',
            $prefix.'[firstName]' => 'Adam',
            $prefix.'[lastName]' => 'Ministrator',
            $prefix.'[textarea]' => 'Lorem ipsum dolor sit amet',
        ]);

        $this->assertHttpStatusCode(302, $this->client->getResponse());

        /** @var AkismetRequestInterface[] $akismetRequests */
        $akismetRequests = $this->entityManager->getRepository(AkismetRequestInterface::class)->findAll();

        self::assertCount(0, $akismetRequests);
    }

    public function testSubmitFormWithoutAkismetConfiguration(): void
    {
        $form = $this->createForm();
        $this->createFormField($form, 'email');
        $this->createFormField($form, 'firstName');
        $this->createFormField($form, 'lastName');
        $this->createFormField($form, 'textarea');
        $this->entityManager->flush();
        /** @var int $formId */
        $formId = $form->getId();

        $this->createPage('/test-page', $formId);
        $this->documentManager->flush();

        $crawler = $this->client->request('GET', '/test-page');
        $prefix = $crawler->filter('#sulu-form > form')->first()->attr('name');
        $this->client->submitForm('Submit', [
            $prefix.'[email]' => 'admin@example.com',
            $prefix.'[firstName]' => 'Adam',
            $prefix.'[lastName]' => 'Ministrator',
            $prefix.'[textarea]' => 'Lorem ipsum dolor sit amet',
        ]);

        $this->assertHttpStatusCode(302, $this->client->getResponse());

        /** @var AkismetRequestInterface[] $akismetRequests */
        $akismetRequests = $this->entityManager->getRepository(AkismetRequestInterface::class)->findAll();

        self::assertCount(0, $akismetRequests);
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

    private function createPage(string $url, int $formId): PageDocument
    {
        /** @var PageDocument $document */
        $document = $this->documentManager->create('page');
        $document->setTitle('Test page');
        $document->setResourceSegment($url);
        $document->setStructureType('default');
        $document->setParent(
            $this->documentManager->find('/cmf/sulu/contents')
        );
        $document->getStructure()->bind([
            'form' => $formId,
        ], false);

        $this->documentManager->persist($document, 'en');
        $this->documentManager->publish($document, 'en');

        return $document;
    }

    private function createForm(): Form
    {
        $form = new Form();
        $form->setDefaultLocale('en');

        $translation = new FormTranslation();
        $translation->setTitle('Test form');
        $translation->setLocale($form->getDefaultLocale());
        $translation->setFromEmail('from@example.com');
        $translation->setToEmail('to@example.com');
        $translation->setDeactivateCustomerMails(true);
        $translation->setDeactivateNotifyMails(true);
        $translation->setForm($form);
        $form->addTranslation($translation);

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
