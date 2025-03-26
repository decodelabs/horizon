<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon\Property;

use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

interface LinkCollection
{
    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    public array $links { get; }

    /**
     * @param array<string,string|int|float|bool|null> $attributes
     * @return $this
     */
    public function addLink(
        string $key,
        ?Tag $tag = null,
        int $priority = 0,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static;

    public function hasLink(
        string $key
    ): bool;

    public function getLink(
        string $key
    ): ?Tag;

    public function getLinkPriority(
        string $key
    ): ?int;

    /**
     * @return $this
     */
    public function removeLink(
        string $key
    ): static;

    /**
     * @return array<string,Tag>
     */
    public function getLinks(): array;

    /**
     * @return $this
     */
    public function clearLinks(): static;
}
