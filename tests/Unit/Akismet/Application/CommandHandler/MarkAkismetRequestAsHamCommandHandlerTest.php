<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsHamCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\MarkAkismetRequestAsHamCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\MarkAkismetRequestAsHamCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\MarkAkismetRequestAsHamCommand
 */
class MarkAkismetRequestAsHamCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
    private ObjectProphecy $repository;

    /** @var ObjectProphecy<AkismetApiInterface> */
    private ObjectProphecy $api;

    private MarkAkismetRequestAsHamCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
        $repository = $this->repository = self::prophesize(AkismetRequestRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetApiInterface> */
        $api = $this->api = self::prophesize(AkismetApiInterface::class);

        $this->commandHandler = new MarkAkismetRequestAsHamCommandHandler(
            $repository->reveal(),
            $api->reveal(),
        );
    }

    public function testInvoke(): void
    {
        /** @var ObjectProphecy<AkismetRequestInterface> $akismetRequest */
        $akismetRequest = self::prophesize(AkismetRequestInterface::class);
        $this->repository->getById(1)->willReturn($akismetRequest->reveal());

        $akismetRequest->setSpam(false)->shouldBeCalled();

        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $akismetRequest->getAkismetConfiguration()->willReturn($akismetConfiguration->reveal());
        $akismetRequest->getRequestParams()->willReturn(['foo' => 'bar']);

        $this->api->submitHam(
            $akismetConfiguration->reveal(),
            ['foo' => 'bar'],
        );

        $command = new MarkAkismetRequestAsHamCommand(1);
        $this->commandHandler->__invoke($command);
    }
}
