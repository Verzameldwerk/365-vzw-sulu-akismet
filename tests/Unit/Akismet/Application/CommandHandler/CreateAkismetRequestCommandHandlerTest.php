<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\CommandHandler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetRequestCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\CreateAkismetRequestCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\CreateAkismetRequestCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetRequestCommand
 */
class CreateAkismetRequestCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
    private ObjectProphecy $configurationRepository;

    /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
    private ObjectProphecy $requestRepository;

    /** @var ObjectProphecy<AkismetApiInterface> */
    private ObjectProphecy $api;

    private CreateAkismetRequestCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
        $configurationRepository = $this->configurationRepository = $this->prophesize(AkismetConfigurationRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
        $requestRepository = $this->requestRepository = $this->prophesize(AkismetRequestRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetApiInterface> */
        $api = $this->api = $this->prophesize(AkismetApiInterface::class);

        $this->commandHandler = new CreateAkismetRequestCommandHandler(
            $configurationRepository->reveal(),
            $requestRepository->reveal(),
            $api->reveal(),
        );
    }

    public function testInvokeResultHam(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->configurationRepository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $this->api->checkComment($akismetConfiguration, ['foo' => 'bar', 'blog' => 'site-url'])->shouldBeCalled()->willReturn('ham');

        /** @var ObjectProphecy<AkismetRequestInterface> $akismetRequest */
        $akismetRequest = $this->prophesize(AkismetRequestInterface::class);
        $this->requestRepository->create($akismetConfiguration->reveal(), ['foo' => 'bar', 'blog' => 'site-url'], false)
            ->shouldBeCalled()
            ->willReturn($akismetRequest->reveal());
        $this->requestRepository->add($akismetRequest)->shouldBeCalled();

        $command = new CreateAkismetRequestCommand(1, ['foo' => 'bar']);
        self::assertFalse($this->commandHandler->__invoke($command));
    }

    public function testInvokeResultSpam(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->configurationRepository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $this->api->checkComment($akismetConfiguration, ['foo' => 'bar', 'blog' => 'site-url'])->shouldBeCalled()->willReturn('spam');

        /** @var ObjectProphecy<AkismetRequestInterface> $akismetRequest */
        $akismetRequest = $this->prophesize(AkismetRequestInterface::class);
        $this->requestRepository->create($akismetConfiguration->reveal(), ['foo' => 'bar', 'blog' => 'site-url'], true)
            ->shouldBeCalled()
            ->willReturn($akismetRequest->reveal());
        $this->requestRepository->add($akismetRequest)->shouldBeCalled();

        $command = new CreateAkismetRequestCommand(1, ['foo' => 'bar']);
        self::assertTrue($this->commandHandler->__invoke($command));
    }

    public function testInvokeResultDiscard(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->configurationRepository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $this->api->checkComment($akismetConfiguration, ['foo' => 'bar', 'blog' => 'site-url'])->shouldBeCalled()->willReturn('discard');

        /** @var ObjectProphecy<AkismetRequestInterface> $akismetRequest */
        $akismetRequest = $this->prophesize(AkismetRequestInterface::class);
        $this->requestRepository->create($akismetConfiguration->reveal(), ['foo' => 'bar', 'blog' => 'site-url'], true)
            ->shouldBeCalled()
            ->willReturn($akismetRequest->reveal());
        $this->requestRepository->add($akismetRequest)->shouldBeCalled();

        $command = new CreateAkismetRequestCommand(1, ['foo' => 'bar']);
        self::assertTrue($this->commandHandler->__invoke($command));
    }

    public function testInvokeOtherResult(): void
    {
        $this->expectException(\LogicException::class);

        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->configurationRepository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $this->api->checkComment($akismetConfiguration, ['foo' => 'bar', 'blog' => 'site-url'])->shouldBeCalled()->willReturn('other');

        $command = new CreateAkismetRequestCommand(1, ['foo' => 'bar']);
        $this->commandHandler->__invoke($command);
    }
}
