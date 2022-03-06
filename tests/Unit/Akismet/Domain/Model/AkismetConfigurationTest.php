<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Domain\Model;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\FormBundle\Entity\FormField;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration
 */
class AkismetConfigurationTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @doesNotPerformAssertions
     */
    public function testConstruct(): void
    {
        $form = $this->createForm();
        $akismetConfiguration = new AkismetConfiguration($form);
    }

    public function testGetSetActive(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();

        self::assertFalse($akismetConfiguration->isActive());
        $akismetConfiguration->setActive(true);
        self::assertTrue($akismetConfiguration->isActive());
    }

    public function testGetSetSiteUrl(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();

        self::assertNull($akismetConfiguration->getSiteUrl());
        $akismetConfiguration->setSiteUrl('https://example.com');
        self::assertSame('https://example.com', $akismetConfiguration->getSiteUrl());
    }

    public function testGetSetApiKey(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();

        self::assertNull($akismetConfiguration->getApiKey());
        $akismetConfiguration->setApiKey('1234');
        self::assertSame('1234', $akismetConfiguration->getApiKey());
    }

    public function testGetFormId(): void
    {
        $form = $this->createForm(['id' => 1]);
        $akismetConfiguration = $this->createAkismetConfiguration(['form' => $form]);

        self::assertSame(1, $akismetConfiguration->getFormId());
    }

    public function testGetSetAuthorNameField(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();

        self::assertNull($akismetConfiguration->getAuthorNameField());

        $authorNameField = $this->createFormField(['id' => 1]);
        $akismetConfiguration->setAuthorNameField($authorNameField);
        self::assertSame($authorNameField, $akismetConfiguration->getAuthorNameField());
        self::assertSame(1, $akismetConfiguration->getAuthorNameFieldId());
    }

    public function testGetSetAuthorEmailField(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();

        self::assertNull($akismetConfiguration->getAuthorEmailField());

        $authorEmailField = $this->createFormField(['id' => 1]);
        $akismetConfiguration->setAuthorEmailField($authorEmailField);
        self::assertSame($authorEmailField, $akismetConfiguration->getAuthorEmailField());
        self::assertSame(1, $akismetConfiguration->getAuthorEmailFieldId());
    }

    public function testGetSetContentField(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();

        self::assertNull($akismetConfiguration->getContentField());

        $contentField = $this->createFormField(['id' => 1]);
        $akismetConfiguration->setContentField($contentField);
        self::assertSame($contentField, $akismetConfiguration->getContentField());
        self::assertSame(1, $akismetConfiguration->getContentFieldId());
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createFormField(array $data = []): FormField
    {
        /** @var ObjectProphecy<FormField> $formField */
        $formField = $this->prophesize(FormField::class);

        if ($id = $data['id'] ?? null) {
            $formField->getId()->willReturn($id);
        }

        return $formField->reveal();
    }

    /**
     * @param array{
     *     id?: int,
     * } $data
     */
    private function createForm(array $data = []): Form
    {
        /** @var ObjectProphecy<Form> $form */
        $form = $this->prophesize(Form::class);

        if ($id = $data['id'] ?? null) {
            $form->getId()->willReturn($id);
        }

        return $form->reveal();
    }

    /**
     * @param array{
     *     form?: Form,
     * } $data
     */
    private function createAkismetConfiguration(array $data = []): AkismetConfiguration
    {
        return new AkismetConfiguration(
            $data['form'] ?? $this->createForm(),
        );
    }
}
