<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\CommandHandler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsSpamCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\MarkAkismetRequestAsSpamCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\MarkAkismetRequestAsSpamCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsSpamCommand
 */
class MarkAkismetRequestAsSpamCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
    private ObjectProphecy $repository;

    /** @var ObjectProphecy<AkismetApiInterface> */
    private ObjectProphecy $api;

    private MarkAkismetRequestAsSpamCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
        $repository = $this->repository = $this->prophesize(AkismetRequestRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetApiInterface> */
        $api = $this->api = $this->prophesize(AkismetApiInterface::class);

        $this->commandHandler = new MarkAkismetRequestAsSpamCommandHandler(
            $repository->reveal(),
            $api->reveal(),
        );
    }

    public function testInvoke(): void
    {
        /** @var ObjectProphecy<AkismetRequestInterface> $akismetRequest */
        $akismetRequest = $this->prophesize(AkismetRequestInterface::class);
        $this->repository->getById(1)->willReturn($akismetRequest->reveal());

        $akismetRequest->setSpam(true)->shouldBeCalled();

        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $akismetRequest->getAkismetConfiguration()->willReturn($akismetConfiguration->reveal());
        $akismetRequest->getRequestParams()->willReturn(['foo' => 'bar']);

        $this->api->submitSpam(
            $akismetConfiguration->reveal(),
            ['foo' => 'bar'],
        );

        $command = new MarkAkismetRequestAsSpamCommand(1);
        $this->commandHandler->__invoke($command);
    }
}
