<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException
 */
class ModelNotFoundExceptionTest extends TestCase
{
    public function testGetMessage(): void
    {
        $exception = $this->createModelNotFoundException([
            'criteria' => ['id' => [1, 2, 3], 'locale' => 'en', 'other' => new \stdClass()],
        ]);
        self::assertSame($exception::getModelName().' with id "[1,2,3]" and locale "en" not found', $exception->getMessage());
    }

    public function testGetModelName(): void
    {
        $exception = $this->createModelNotFoundException([
            'modelName' => 'CustomEntity',
        ]);
        self::assertSame('CustomEntity', $exception::getModelName());
    }

    public function testGetCriteria(): void
    {
        $criteria = ['id' => [1, 2, 3], 'locale' => 'en', 'other' => new \stdClass()];
        $exception = $this->createModelNotFoundException([
            'criteria' => $criteria,
        ]);
        self::assertSame($criteria, $exception->getCriteria());
    }

    /**
     * @param array{
     *     modelName?: string,
     *     criteria?: array<string, mixed>,
     * } $data
     */
    protected function createModelNotFoundException(array $data = []): ModelNotFoundException
    {
        return new class($data) extends ModelNotFoundException {
            /**
             * @var string
             */
            private static $MODEL_NAME;

            /**
             * @param array{
             *     modelName?: string,
             *     criteria?: array<string, mixed>,
             * } $data
             */
            public function __construct(array $data)
            {
                self::$MODEL_NAME = $data['modelName'] ?? 'CustomEntity';
                parent::__construct($data['criteria'] ?? ['id' => 1]);
            }

            public static function getModelName(): string
            {
                return self::$MODEL_NAME;
            }
        };
    }
}
