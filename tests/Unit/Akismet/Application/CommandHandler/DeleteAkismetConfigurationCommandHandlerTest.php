<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeleteAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeleteAkismetConfigurationCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetConfigurationCommand
 */
class DeleteAkismetConfigurationCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
    private ObjectProphecy $repository;

    private DeleteAkismetConfigurationCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
        $repository = $this->repository = self::prophesize(AkismetConfigurationRepositoryInterface::class);

        $this->commandHandler = new DeleteAkismetConfigurationCommandHandler(
            $repository->reveal(),
        );
    }

    public function testInvoke(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $this->repository->getById(1)->willReturn($akismetConfiguration->reveal());

        $this->repository->remove($akismetConfiguration->reveal())->shouldBeCalled();

        $command = new DeleteAkismetConfigurationCommand(1);
        $this->commandHandler->__invoke($command);
    }
}
