<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\CommandHandler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetRequestCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeleteAkismetRequestCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequestInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetRequestRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\DeleteAkismetRequestCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\DeleteAkismetRequestCommand
 */
class DeleteAkismetRequestCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
    private ObjectProphecy $repository;

    private DeleteAkismetRequestCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetRequestRepositoryInterface> */
        $repository = $this->repository = self::prophesize(AkismetRequestRepositoryInterface::class);

        $this->commandHandler = new DeleteAkismetRequestCommandHandler(
            $repository->reveal(),
        );
    }

    public function testInvoke(): void
    {
        /** @var ObjectProphecy<AkismetRequestInterface> $akismetRequest */
        $akismetRequest = self::prophesize(AkismetRequestInterface::class);
        $this->repository->getById(1)->willReturn($akismetRequest->reveal());

        $this->repository->remove($akismetRequest->reveal())->shouldBeCalled();

        $command = new DeleteAkismetRequestCommand(1);
        $this->commandHandler->__invoke($command);
    }
}
