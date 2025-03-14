<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon;

use Closure;
use DecodeLabs\Tagged\Tag;

interface Head extends Renderable
{
    public string|Closure|null $rawTitle { get; set; }
    public ?Closure $titleDecorator { get; set; }
    public string $title { get; set(string|Closure|null $value); }

    /**
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function setMeta(
        string $key,
        string|Tag $value,
        array $attributes = []
    ): static;

    public function hasMeta(
        string $key
    ): bool;

    public function getMeta(
        string $key
    ): ?Tag;

    public function getMetaValue(
        string $key
    ): ?string;
}
