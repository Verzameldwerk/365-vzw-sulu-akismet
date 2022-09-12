<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception;

abstract class ModelNotFoundException extends \Exception
{
    /**
     * @var array<string, mixed>
     */
    private array $criteria;

    /**
     * @param array<string, mixed> $criteria
     */
    public function __construct(array $criteria)
    {
        $this->criteria = $criteria;

        parent::__construct(
            sprintf(
                '%s with %s not found',
                static::getModelName(),
                $this->stringifyCriteria($this->criteria),
            ),
        );
    }

    /**
     * @param array<string, mixed> $criteria
     */
    private function stringifyCriteria(array $criteria): string
    {
        $messageParts = [];

        foreach ($criteria as $key => $value) {
            if (\is_array($value)) {
                $value = json_encode($value);
            }

            if (!\is_scalar($value)) {
                continue;
            }

            $messageParts[] = sprintf('%s "%s"', $key, $value);
        }

        return implode(' and ', $messageParts);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    abstract public static function getModelName(): string;
}
