<?php

/**
 * @package Horizon
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Horizon\Property;

use DecodeLabs\Tagged\PriorityMarkup;
use DecodeLabs\Tagged\Tag;

interface ScriptCollection
{
    /**
     * @var array<string,PriorityMarkup<Tag>>
     */
    public array $scripts { get; }

    /**
     * @param array<string,string|int|float|bool> $attributes
     * @return $this
     */
    public function addScript(
        string $key,
        ?Tag $tag = null,
        int $priority = 0,
        mixed $script = null,
        array $attributes = [],
        string|int|float|bool|null ...$attributeList
    ): static;

    public function hasScript(
        string $key
    ): bool;

    public function getScript(
        string $key
    ): ?Tag;

    public function getScriptPriority(
        string $key
    ): ?int;

    /**
     * @return $this
     */
    public function removeScript(
        string $key
    ): static;

    /**
     * @return array<string,Tag>
     */
    public function getScripts(): array;

    /**
     * @return $this
     */
    public function clearScripts(): static;
}
