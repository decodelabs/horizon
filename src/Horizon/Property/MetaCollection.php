<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon\Property;

use DecodeLabs\Tagged\Tag;

interface MetaCollection
{
    /**
     * @var array<string,Tag>
     */
    public array $meta { get; }

    /**
     * @param iterable<string,string|Tag> $meta
     * @return $this
     */
    public function applyMeta(
        iterable $meta
    ): static;

    /**
     * @param array<string,string|int|float|bool|null> $attributes
     * @return $this
     */
    public function setMeta(
        string $key,
        string|Tag $value,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
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

    /**
     * @return $this
     */
    public function removeMeta(
        string $key
    ): static;

    /**
     * @return $this
     */
    public function clearMeta(): static;
}
