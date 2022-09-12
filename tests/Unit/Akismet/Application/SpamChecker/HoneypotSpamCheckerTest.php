<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\SpamChecker;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\FormBundle\Configuration\FormConfigurationInterface;
use Symfony\Component\Form\FormInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\HoneypotSpamChecker;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\SpamCheckerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamException;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\HoneypotSpamChecker
 */
class HoneypotSpamCheckerTest extends TestCase
{
    use ProphecyTrait;

    private HoneypotSpamChecker $spamChecker;

    protected function setUp(): void
    {
        $this->spamChecker = new HoneypotSpamChecker('honeypot', SpamCheckerInterface::SPAM_STRATEGY_SPAM);
    }

    public function testCheckNoHoneypotField(): void
    {
        $this->expectNotToPerformAssertions();

        $spamChecker = new HoneypotSpamChecker('', null);

        $form = $this->prophesize(FormInterface::class);
        $formConfiguration = $this->prophesize(FormConfigurationInterface::class);

        $spamChecker->check($form->reveal(), $formConfiguration->reveal());
    }

    public function testCheckNoHoneypotFieldInForm(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $formConfiguration = $this->prophesize(FormConfigurationInterface::class);

        $form->has('honeypot')->willReturn(false)->shouldBeCalled();
        $form->get('honeypot')->shouldNotBeCalled();

        $this->spamChecker->check($form->reveal(), $formConfiguration->reveal());
    }

    public function testCheckHoneypotFieldEmpty(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $formConfiguration = $this->prophesize(FormConfigurationInterface::class);

        $honeypotFormField = $this->prophesize(FormInterface::class);
        $honeypotFormField->getData()->willReturn('')->shouldBeCalled();

        $form->has('honeypot')->willReturn(true)->shouldBeCalled();
        $form->get('honeypot')->willReturn($honeypotFormField->reveal())->shouldBeCalled();

        $this->spamChecker->check($form->reveal(), $formConfiguration->reveal());
    }

    public function testCheckIsSpamByHoneypot(): void
    {
        $this->expectExceptionObject(
            new FormSubmissionIsSpamException(SpamCheckerInterface::SPAM_STRATEGY_SPAM, HoneypotSpamChecker::SPAM_REASON_HONEYPOT)
        );

        $form = $this->prophesize(FormInterface::class);
        $formConfiguration = $this->prophesize(FormConfigurationInterface::class);

        $honeypotFormField = $this->prophesize(FormInterface::class);
        $honeypotFormField->getData()->willReturn('user@example.com')->shouldBeCalled();

        $form->has('honeypot')->willReturn(true)->shouldBeCalled();
        $form->get('honeypot')->willReturn($honeypotFormField->reveal())->shouldBeCalled();

        $this->spamChecker->check($form->reveal(), $formConfiguration->reveal());
    }
}
