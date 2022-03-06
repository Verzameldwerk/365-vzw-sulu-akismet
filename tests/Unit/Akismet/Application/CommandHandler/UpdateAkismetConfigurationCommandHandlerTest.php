<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\CommandHandler;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\UpdateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\UpdateAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapperInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException;
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
        $repository = $this->repository = $this->prophesize(AkismetConfigurationRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetConfigurationDataMapperInterface> */
        $dataMapper = $this->dataMapper = $this->prophesize(AkismetConfigurationDataMapperInterface::class);
        /** @var ObjectProphecy<AkismetApiInterface> */
        $api = $this->api = $this->prophesize(AkismetApiInterface::class);

        $this->commandHandler = new UpdateAkismetConfigurationCommandHandler(
            $repository->reveal(),
            $dataMapper->reveal(),
            $api->reveal(),
        );
    }

    public function testInvokeWithApiKeyAndSiteUrlActivate(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(false, true)->shouldBeCalledTimes(2);

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

    public function testInvokeWithApiKeyAndSiteUrlKeepActive(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(true, true)->shouldBeCalledTimes(2);

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

    public function testInvokeWithApiKeyAndSiteUrlDeactivate(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(true, false)->shouldBeCalledTimes(2);

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

    public function testInvokeWithApiKeyAndSiteUrlKeepInactive(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(false, false)->shouldBeCalledTimes(2);

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

    /**
     * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException
     */
    public function testInvokeWithoutApiKeyActivate(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(false, true)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn(null);
        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $akismetConfiguration->setActive(Argument::any())->shouldNotBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::expectException(AkismetApiException::class);
        self::expectExceptionMessage('Cannot activate akismet configuration, if api key is empty');

        $this->commandHandler->__invoke($command);
    }

    public function testInvokeWithoutApiKeyKeepActive(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(true, true)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn(null);
        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }

    public function testInvokeWithoutApiKeyDeactivate(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(true, false)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn(null);
        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }

    public function testInvokeWithoutApiKeyKeepInactive(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(false, false)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn(null);
        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }

    /**
     * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException
     */
    public function testInvokeWithoutSiteUrlActivate(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(false, true)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn('api-key');
        $akismetConfiguration->getSiteUrl()->willReturn(null);
        $akismetConfiguration->setActive(Argument::any())->shouldNotBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::expectException(AkismetApiException::class);
        self::expectExceptionMessage('Cannot activate akismet configuration, if site url is empty');

        $this->commandHandler->__invoke($command);
    }

    public function testInvokeWithoutSiteUrlKeepActive(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(true, true)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn('api-key');
        $akismetConfiguration->getSiteUrl()->willReturn(null);
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }

    public function testInvokeWithoutSiteUrlDeactivate(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(true, false)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn('api-key');
        $akismetConfiguration->getSiteUrl()->willReturn(null);
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }

    public function testInvokeWithoutSiteUrlKeepInactive(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());
        $akismetConfiguration->isActive()->willReturn(false, false)->shouldBeCalledTimes(2);

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn('api-key');
        $akismetConfiguration->getSiteUrl()->willReturn(null);
        $akismetConfiguration->setActive(false)->shouldBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldNotBeCalled();

        $command = new UpdateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }
}
