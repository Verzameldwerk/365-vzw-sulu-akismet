<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\FormBundle\Entity\FormField;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapper;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapperInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapper
 */
class AkismetConfigurationDataMapperTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<EntityManagerInterface>
     */
    private ObjectProphecy $entityManager;

    private AkismetConfigurationDataMapperInterface $dataMapper;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<EntityManagerInterface> $entityManager */
        $entityManager = $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->dataMapper = new AkismetConfigurationDataMapper(
            $entityManager->reveal()
        );
    }

    public function testMapData(): void
    {
        $authorNameField = $this->prophesize(FormField::class)->reveal();
        $this->entityManager->getReference(FormField::class, 1)->willReturn($authorNameField);
        $authorEmailField = $this->prophesize(FormField::class)->reveal();
        $this->entityManager->getReference(FormField::class, 2)->willReturn($authorEmailField);
        $contentField = $this->prophesize(FormField::class)->reveal();
        $this->entityManager->getReference(FormField::class, 3)->willReturn($contentField);

        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $akismetConfiguration->setActive(true)->shouldBeCalled();
        $akismetConfiguration->setSiteUrl('siteUrl')->shouldBeCalled();
        $akismetConfiguration->setApiKey('apiKey')->shouldBeCalled();
        $akismetConfiguration->setAuthorNameField($authorNameField)->shouldBeCalled();
        $akismetConfiguration->setAuthorEmailField($authorEmailField)->shouldBeCalled();
        $akismetConfiguration->setContentField($contentField)->shouldBeCalled();

        $this->dataMapper->mapData($akismetConfiguration->reveal(), [
            'active' => true,
            'siteUrl' => 'siteUrl',
            'apiKey' => 'apiKey',
            'authorNameField' => 1,
            'authorEmailField' => 2,
            'contentField' => 3,
        ]);
    }

    public function testMapDataNullValues(): void
    {
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $akismetConfiguration->setSiteUrl(null)->shouldBeCalled();
        $akismetConfiguration->setApiKey(null)->shouldBeCalled();
        $akismetConfiguration->setAuthorNameField(null)->shouldBeCalled();
        $akismetConfiguration->setAuthorEmailField(null)->shouldBeCalled();
        $akismetConfiguration->setContentField(null)->shouldBeCalled();

        $this->dataMapper->mapData($akismetConfiguration->reveal(), [
            'active' => false,
            'siteUrl' => null,
            'apiKey' => null,
            'authorNameField' => null,
            'authorEmailField' => null,
            'contentField' => null,
        ]);
    }
}
