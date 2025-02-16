<?php

declare(strict_types=1);

namespace App\Transaction\UserInterface\Web;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Route
{
    public function __construct(
        private array|Method $method,
        private string $pattern,
        private ?string $alias = null,
    ) {
    }

    public function getMethod(): array|Method
    {
        return $this->method;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
