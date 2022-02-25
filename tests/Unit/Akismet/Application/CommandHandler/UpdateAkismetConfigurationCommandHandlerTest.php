<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\UpdateAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapperInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\UpdateAkismetConfigurationCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand
 */
class UpdateAkismetConfigurationCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
    private ObjectProphecy $repository;

    /** @var ObjectProphecy<AkismetConfigurationDataMapperInterface> */
    private ObjectProphecy $dataMapper;

    /** @var ObjectProphecy<AkismetApiInterface> */
    private ObjectProphecy $api;

    private UpdateAkismetConfigurationCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
        $repository = $this->repository = self::prophesize(AkismetConfigurationRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetConfigurationDataMapperInterface> */
        $dataMapper = $this->dataMapper = self::prophesize(AkismetConfigurationDataMapperInterface::class);
        /** @var ObjectProphecy<AkismetApiInterface> */
        $api = $this->api = self::prophesize(AkismetApiInterface::class);

        $this->commandHandler = new UpdateAkismetConfigurationCommandHandler(
            $repository->reveal(),
            $dataMapper->reveal(),
            $api->reveal(),
        );
    }

    public function testInvokeWithApiKeyAndSiteUrl(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn('api-key');
        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $akismetConfiguration->setActive(Argument::any())->shouldNotBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }

    public function testInvokeWithoutApiKeyAndSiteUrl(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn(null);
        $akismetConfiguration->getSiteUrl()->willReturn(null);
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $this->api->verifyKey(Argument::any())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }
}
