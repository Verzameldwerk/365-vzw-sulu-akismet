<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\ActivateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\ActivateAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\ActivateAkismetConfigurationCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\ActivateAkismetConfigurationCommand
 */
class ActivateAkismetConfigurationCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
    private ObjectProphecy $repository;

    /** @var ObjectProphecy<AkismetApiInterface> */
    private ObjectProphecy $api;

    private ActivateAkismetConfigurationCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
        $repository = $this->repository = self::prophesize(AkismetConfigurationRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetApiInterface> */
        $api = $this->api = self::prophesize(AkismetApiInterface::class);

        $this->commandHandler = new ActivateAkismetConfigurationCommandHandler(
            $repository->reveal(),
            $api->reveal(),
        );
    }

    public function testInvoke(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());

        $akismetConfiguration->setActive(true)->shouldBeCalled();
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldBeCalled();

        $command = new ActivateAkismetConfigurationCommand(1);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }
}
