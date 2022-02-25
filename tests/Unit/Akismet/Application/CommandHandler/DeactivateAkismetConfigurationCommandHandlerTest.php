<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeactivateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeactivateAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeactivateAkismetConfigurationCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeactivateAkismetConfigurationCommand
 */
class DeactivateAkismetConfigurationCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
    private ObjectProphecy $repository;

    private DeactivateAkismetConfigurationCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
        $repository = $this->repository = self::prophesize(AkismetConfigurationRepositoryInterface::class);

        $this->commandHandler = new DeactivateAkismetConfigurationCommandHandler(
            $repository->reveal()
        );
    }

    public function testInvoke(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());

        $akismetConfiguration->setActive(false)->shouldBeCalled();

        $command = new DeactivateAkismetConfigurationCommand(1);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }
}
